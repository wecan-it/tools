<?php

use Wecan\Tools\Html\FormBuilder;

class Helper
{
    /**
     * Add an element to an array if it doesn't exist.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    public static function array_add($array, $key, $value)
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Divide an array into two arrays. One with keys and the other with values.
     *
     * @param  array $array
     * @return array
     */
    public static function array_divide($array)
    {
        return array(array_keys($array), array_values($array));
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array $array
     * @param  array $keys
     * @return array
     */
    public static function array_except($array, $keys)
    {
        return array_diff_key($array, array_flip((array)$keys));
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array $array
     * @param  \Closure $callback
     * @param  mixed $default
     * @return mixed
     */
    public static function array_first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return self::value($default);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array $array
     * @param  \Closure $callback
     * @param  mixed $default
     * @return mixed
     */
    public static function array_last($array, $callback, $default = null)
    {
        return self::array_first(array_reverse($array), $callback, $default);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array $array
     * @return array
     */
    public static function array_flatten($array)
    {
        $return = array();

        array_walk_recursive($array, function ($x) use (&$return) {
            $return[] = $x;
        });

        return $return;
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array $array
     * @param  array $keys
     * @return array
     */
    public static function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param  array $array
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public static function array_pluck($array, $value, $key = null)
    {
        $results = array();

        foreach ($array as $item) {
            $itemValue = is_object($item) ? $item->{$value} : $item[$value];

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) ? $item->{$key} : $item[$key];

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Get a value from the array, and remove it.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function array_pull(&$array, $key, $default = null)
    {
        $value = self::array_get($array, $key, $default);

        self::array_forget($array, $key);

        return $value;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return self::value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array $array
     * @param  array|string $keys
     * @return void
     */
    public static function array_forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array)$keys as $key) {
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }

    /**
     * Filter the array using the given Closure.
     *
     * @param  array $array
     * @param  \Closure $callback
     * @return array
     */
    public static function array_where($array, \Closure $callback)
    {
        $filtered = array();

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Dump the passed variables and end the script.
     *
     * @param  dynamic  mixed
     * @return void
     */
    public static function dd()
    {
        $args = func_get_args();
        $style = "";
        if (!$GLOBALS['USER']->IsAdmin()) {
            $style = 'style="display:none;"';
        }
        array_map(function ($x) use ($style) {
            echo "<noindex $style>";
            dump($x);
            echo "</noindex>";
        }, $args);
    }

    /**
     * Dump the passed variables and end the script.
     *
     * @param  dynamic  mixed
     * @return void
     */
    public static function dump()
    {
        $args = func_get_args();
        $style = "";
        if (!$GLOBALS['USER']->IsAdmin()) {
            $style = 'style="display:none;"';
        }
        array_map(function ($x) use ($style) {
            echo "<noindex><pre " . $style . ">";
            dump($x);
            echo "</pre></noindex>";
        }, $args);
    }

    public static function includeFile($file, $name = null)
    {
        $includePathFull = $_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/' . $file . ".php";
        $includePathRel = SITE_DIR . "include/" . $file . ".php";
        if (!file_exists($includePathFull)) {
            $newFile = fopen($includePathFull, 'w');
            fclose($newFile);
        } else {
            $GLOBALS['APPLICATION']->IncludeFile(
                $includePathRel,
                array(),
                array("MODE" => "html", 'NAME' => $name)
            );
        }
    }

    public static function pluralize($n, Array $forms)
    {
        if (count($forms) < 3) {
            $forms = array_fill(0, 3, $forms[0]);
        }

        return $n % 10 == 1 && $n % 100 != 11 ? $forms[0] : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $forms[1] : $forms[2]);
    }

    /**
     * Get the first element of an array. Useful for method chaining.
     *
     * @param  array $array
     * @return mixed
     */
    public static function head($array)
    {
        return reset($array);
    }

    /**
     * Get the last element from an array.
     *
     * @param  array $array
     * @return mixed
     */
    public static function last($array)
    {
        return end($array);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle == substr($haystack, -strlen($needle))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    public static function addJs($arJs)
    {
        if (!is_array($arJs)) {
            $arJs = func_get_args();
        }
        self::registerAssets($arJs, 'js');
    }

    public static function addCss($arCss)
    {
        if (!is_array($arCss)) {
            $arCss = func_get_args();
        }
        self::registerAssets($arCss, 'css');
    }

    private static function registerAssets($assets, $type)
    {
        $assetsManager = \Bitrix\Main\Page\Asset::getInstance();

        array_map(function ($val) use ($type, $assetsManager) {

            $startsWithSlash = false;
            foreach ((array)"/" as $needle) {
                if ($needle != '' && strpos($val, $needle) === 0) {
                    $startsWithSlash = true;
                }
            }

            if (!$startsWithSlash && !\CMain::isExternalLink($val) && defined('DEFAULT_TEMPLATE_PATH')) {
                if (!self::endsWith(DEFAULT_TEMPLATE_PATH, '/')) {
                    $val = '/' . $val;
                }
                $val = DEFAULT_TEMPLATE_PATH . $val;
            }

            switch ($type) {
                case 'js':
                    $assetsManager->addJs($val);
                    break;
                case 'css':
                    $assetsManager->addCss($val);
                    break;
                default:
                    break;
            }
        }, $assets);


    }

    public static function form_tag($options, $block)
    {
        $url = array_key_exists('action', $options)
            ? self::array_pull($options, 'action')
            : POST_FORM_ACTION_URI;
        $html_options = self::html_options_for_form($url, $options);

        return self::form_html($html_options, $block);
    }

    public static function form_for($component_result, $options, $block)
    {
        $url = array_key_exists('action', $options)
            ? self::array_pull($options, 'action')
            : POST_FORM_ACTION_URI;
        $html_options = self::html_options_for_form($url, $options);

        return self::form_html($html_options, $block, $component_result);
    }

    private static function html_options_for_form($url, $options)
    {
        $html = array();

        foreach ($options as $key => $value) {
            $element = null;
            if (is_numeric($key)) {
                $key = $value;
            }

            if (!is_null($value)) {
                $element = $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8', false) . '"';
            }

            if (!is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' action="' . $url . '" ' . implode(' ', $html) : '';

    }

    private static function get_form_tag_html($html_options)
    {
        return '<form' . $html_options . '>';
    }

    private static function form_html($html_options, $block, $result = null)
    {
        $form_tag_html = self::get_form_tag_html($html_options);
        ob_start();
        if ($block instanceof \Closure) {
            $block(new FormBuilder($result));
        }
        $form_content = ob_get_contents();
        ob_end_clean();

        return $form_tag_html . $form_content . "</form>";
    }

    public static function get_from_cache(...$params)
    {
        $paramsCount = count($params);
        if ($paramsCount < 2 || $paramsCount > 3 || ($paramsCount == 3 && !($params[2] instanceof Closure))) {
            throw new Exception('invalid params');
        }
        if ($paramsCount == 2) {
            list($key, $block) = $params;
            $expires_in = 3600;
        }
        if ($paramsCount == 3) {
            list($key, $expires_in, $block) = $params;
        }
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache($expires_in, $key)) {
            $cachedVars = $cache->getVars();

            return $cachedVars['res'];
        } elseif ($cache->startDataCache()) {
            if ($block) {
                $res = $block();
            } else {
                $res = false;
            }
            $cache->endDataCache(array('res' => $res));

            return $res;
        }

        return false;
    }

    public static function isXhrRequest()
    {
        return (\Bitrix\Main\Context::getCurrent()->getRequest()->isAjaxRequest());
    }

    public static function isOnHomePage()
    {
        return $GLOBALS['APPLICATION']->GetCurPage(false) === '/';
    }

    public static function depthUrl($url)
    {
        $ar = explode('/', $url);

        $depth = count($ar) - 2;

        return $depth;
    }

    /**
     * get hightloadblock class
     */
    public static function getHLClass($id)
    {
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($id)->fetch();
        $entityClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
        return $entityClass;
    }

    /**
     * get HL class by name
     * @param $name
     * @return \Bitrix\Main\ORM\Data\DataManager
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getHLClassByName($name)
    {
        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter' => array('NAME' => $name)))->fetch();
        $entityClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
        return $entityClass;
    }

    public static function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }

    public static function toLog(string $file, $data, $fileAppend = 0) {
        if (is_array($data))
            $data = json_encode($data);

        if ($fileAppend)
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $file, "\n", FILE_APPEND);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $file, $data, $fileAppend? FILE_APPEND : 0);
    }
}