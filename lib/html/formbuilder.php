<?php namespace Wecan\Tools\Html;

class FormBuilder
{
    /**
     * Class of inputs with error
     *
     * @var string
     */
    protected $errorClass = 'error';

    /**
     * Error messages formatter
     *
     * @var callable
     */
    protected $errorMessagesFormatCallback = null;

    /**
     * @var ContentTag
     */
    protected $errorMessagesWrapper = null;

    /**
     * component`s arResult
     *
     * @var array
     */
    protected $result;

    protected $skipValueTypes = array('file', 'password', 'checkbox', 'radio');

    /**
     * @param $component_result
     */
    public function __construct($component_result) {
        $this->result = $component_result;

        $this->errorMessagesFormatCallback = function ($message) {
            return "{$message}<br>";
        };

        $this->errorMessagesWrapper = new ContentTag("div", "", array("class" => "form_field_error_messages"));
    }

    /**
     * @param string $errorClass
     */
    public function setErrorClass($errorClass) {
        $this->errorClass = $errorClass;
    }

    public function setErrorMessagesWrapper(ContentTag $wrapper) {
        $this->errorMessagesWrapper = $wrapper;
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function label($name, $value = null, $options = array()) {
        return new Label($name, $value, $options);
    }

    /**
     * @param  string $type
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function input($type, $name, $value = null, $options = array(), $setResultAttributes = true) {
        if(!is_null($this->result) && $setResultAttributes) {
            $this->setResultAttributes($name, $value, $options);
        }

        if(!isset($options['name'])) $options['name'] = $name;

        $id = $this->getIdAttribute($name, $options);

        if(!in_array($type, $this->skipValueTypes)) {
            $value = $this->getValueAttribute($name, $value);
        }

        $merge = compact('type', 'value', 'id');

        $options = array_merge($options, $merge);

        return '<input' . $this->attributes($options) . '>';
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function text($name, $value = null, $options = array()) {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * @param  string $name
     * @param  array $options
     * @return string
     */
    public function password($name, $options = array()) {
        return $this->input('password', $name, '', $options);
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function hidden($name, $value = null, $options = array()) {
        return $this->input('hidden', $name, $value, $options);
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function email($name, $value = null, $options = array()) {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function url($name, $value = null, $options = array()) {
        return $this->input('url', $name, $value, $options);
    }

    /**
     * @param  string $name
     * @param  array $options
     * @return string
     */
    public function file($name, $options = array()) {
        if(!is_null($this->result)) $name = "PROPERTY_" . $name;

        return $this->input('file', $name, null, $options, false);
    }

    /**
     * @param  string $name
     * @param  string $value
     * @param  array $options
     * @return string
     */
    public function textarea($name, $value = null, $options = array()) {
        if(!is_null($this->result)) {
            $this->setResultAttributes($name, $value, $options);
        }

        if(!isset($options['name'])) $options['name'] = $name;

        $options['id'] = $this->getIdAttribute($name, $options);

        $value = (string)$this->getValueAttribute($name, $value);

        if(isset($options['size'])) {
            $options = $this->setQuickTextAreaSize($options);
            unset($options['size']);
        }

        $options = $this->attributes($options);

        return '<textarea' . $options . '>' . $value . '</textarea>';
    }

    /**
     * @param  array $options
     * @return array
     */
    protected function setQuickTextAreaSize($options) {
        $segments = explode('x', $options['size']);

        return array_merge($options, array('cols' => $segments[0], 'rows' => $segments[1]));
    }

    /**
     * Create a select box field.
     *
     * @param  string $name
     * @param  array $list
     * @param  string $selected
     * @param  array $options
     * @return string
     */
    public function select($name, $list = array(), $selected = null, $options = array()) {
        if(!is_null($this->result)) {
            if(empty($list)) {
                $list = array_combine(
                    array_keys($this->result["PROPERTY_LIST_FULL"][$name]["ENUM"]),
                    \Helper::array_pluck($this->result["PROPERTY_LIST_FULL"][$name]["ENUM"], 'VALUE')
                );
            }

            $selected = $this->result['ELEMENT_PROPERTIES'][$name] ?: $selected;
            $name = "PROPERTY[{$name}]";

        }

        $selected = $this->getValueAttribute($name, $selected);

        $options['id'] = $this->getIdAttribute($name, $options);

        if(!isset($options['name'])) $options['name'] = $name;

        $html = array();

        foreach($list as $value => $display) {
            $html[] = $this->getSelectOption($display, $value, $selected);
        }

        $options = $this->attributes($options);

        $list = implode('', $html);

        return "<select{$options}>{$list}</select>";
    }

    /**
     * Create a select range field.
     *
     * @param  string $name
     * @param  string $begin
     * @param  string $end
     * @param  string $selected
     * @param  array $options
     * @return string
     */
    public function selectRange($name, $begin, $end, $selected = null, $options = array()) {
        $range = array_combine($range = range($begin, $end), $range);

        return $this->select($name, $range, $selected, $options);
    }

    /**
     * Get the select option for the given value.
     *
     * @param  string $display
     * @param  string $value
     * @param  string $selected
     * @return string
     */
    public function getSelectOption($display, $value, $selected) {
        if(is_array($display)) {
            return $this->optionGroup($display, $value, $selected);
        }

        return $this->option($display, $value, $selected);
    }

    /**
     * Create an option group form element.
     *
     * @param  array $list
     * @param  string $label
     * @param  string $selected
     * @return string
     */
    protected function optionGroup($list, $label, $selected) {
        $html = array();

        foreach($list as $value => $display) {
            $html[] = $this->option($display, $value, $selected);
        }

        return '<optgroup label="' . $label . '">' . implode('', $html) . '</optgroup>';
    }

    /**
     * Create a select element option.
     *
     * @param  string $display
     * @param  string $value
     * @param  string $selected
     * @return string
     */
    protected function option($display, $value, $selected) {
        $selected = $this->getSelectedValue($value, $selected);

        $options = array('value' => $value, 'selected' => $selected);

        return '<option' . $this->attributes($options) . '>' . $display . '</option>';
    }

    /**
     * Determine if the value is selected.
     *
     * @param  string $value
     * @param  string $selected
     * @return string
     */
    protected function getSelectedValue($value, $selected) {
        if(is_array($selected)) {
            return in_array($value, $selected) ? 'selected' : null;
        }

        return ((string)$value == (string)$selected) ? 'selected' : null;
    }

    /**
     * Create a checkbox input field.
     *
     * @param  string $name
     * @param  mixed $value
     * @param  bool $checked
     * @param  array $options
     * @return string
     */
    public function checkbox($name, $value = 1, $checked = null, $options = array()) {
        return $this->checkable('checkbox', $name, $value, $checked, $options);
    }

    public function radio($name, $value = null, $checked = null, $options = array()) {
        if(is_null($value)) $value = $name;

        return $this->checkable('radio', $name, $value, $checked, $options);
    }

    protected function checkable($type, $name, $value, $checked, $options) {
        $checked = $this->getCheckedState($type, $name, $value, $checked);

        if($checked) $options['checked'] = 'checked';

        return $this->input($type, $name, $value, $options, false);
    }

    protected function getCheckedState($type, $name, $value, $checked) {
        switch($type) {
            case 'checkbox':
                return $this->getCheckboxCheckedState($name, $value);

            case 'radio':
                return $this->getRadioCheckedState($value, $checked);

            default:
                return $this->getValueAttribute($name) == $value;
        }
    }

    protected function getCheckboxCheckedState($name, $value) {
        $posted = $this->getValueAttribute($name);

        return is_array($posted) ? in_array($value, $posted) : (bool)$posted;
    }

    protected function getRadioCheckedState($value, $checked) {
        return $checked == $value;
    }

    public function reset($value, $attributes = array()) {
        return $this->input('reset', null, $value, $attributes);
    }

    public function image($url, $name = null, $attributes = array()) {
        $attributes['src'] = $url;

        return $this->input('image', $name, null, $attributes);
    }

    public function submit($value = null, $options = array()) {
        return $this->input('submit', null, $value, $options, false);
    }

    public function button($value = null, $options = array()) {
        if(!array_key_exists('type', $options)) {
            $options['type'] = 'button';
        }

        return '<button' . $this->attributes($options) . '>' . $value . '</button>';
    }

    public function getIdAttribute($name, $attributes) {
        if(array_key_exists('id', $attributes)) {
            return $attributes['id'];
        }

        return is_null($this->result) ? $name : $this->result['AJAX_ID'] . "_" . $name;
    }

    public function getValueAttribute($name, $value = null) {
        if(is_null($name)) return $value;

        if(!is_null($value)) return $value;

        return null;
    }

    protected function transformKey($key) {
        return str_replace(array('.', '[]', '[', ']'), array('_', '', '.', ''), $key);
    }

    public function attributes($attributes) {
        $html = array();

        // For numeric keys we will assume that the key and the value are the same
        // as this will convert HTML attributes such as "required" to a correct
        // form like required="required" instead of using incorrect numerics.
        foreach((array)$attributes as $key => $value) {
            $element = $this->attributeElement($key, $value);

            if(!is_null($element)) $html[] = $element;
        }

        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    public function captchaImage($width = 180, $height = 40, $attributes) {
        if(is_null($this->result)) return false;
        $input = $this->input('hidden', "captcha_sid_" . $this->result['AJAX_ID'], $this->result['CAPTCHA_CODE'], array(), false);
        $image = '<img src="/bitrix/tools/captcha.php?captcha_sid=' . $this->result["CAPTCHA_CODE"] . '" width="' . $width . '" height="' . $height . '" alt="CAPTCHA"' . $this->attributes($attributes) .'/>';

        return $input . $image;
    }

    public function captchaInput($options) {
        if(array_key_exists('CAPTCHA', $this->result['ERRORS'])) {
            isset($options['class'])
                ? $options['class'] .= ' ' . $this->errorClass
                : $options['class'] = $this->errorClass;
        }

        return $this->input('text', 'captcha_word_' . $this->result['AJAX_ID'], "", $options, false);
    }

    /**
     * @param  string $key
     * @param  string $value
     * @return string
     */
    protected function attributeElement($key, $value) {
        if(is_numeric($key)) $key = $value;

        if(!is_null($value)) return $key . '="' . $this->e($value) . '"';
    }

    /**
     * @param  string $value
     * @return string
     */
    protected function e($value) {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * @param $name
     * @param $value
     * @param $options
     */
    protected function setResultAttributes(&$name, &$value, &$options) {
        if(isset($this->result['ERRORS'][$name])) {
            isset($options['class'])
                ? $options['class'] .= ' ' . $this->errorClass
                : $options['class'] = $this->errorClass;
        }
        if($this->result['ELEMENT_PROPERTIES']) $value = $this->result['ELEMENT_PROPERTIES'][$name];
        $name = "PROPERTY[{$name}]";
    }

    public function collectionRadioButtons($name, $list, $checked, $options, \Closure $block = null) {
        if(!is_null($this->result)) {
            $list = array_combine(
                array_keys($this->result["PROPERTY_LIST_FULL"][$name]["ENUM"]),
                \Helper::array_pluck($this->result["PROPERTY_LIST_FULL"][$name]["ENUM"], 'VALUE')
            );
            $checked = $this->result['ELEMENT_PROPERTIES'][$name] ?: $checked;
            $name = "PROPERTY[{$name}]";
        }

        $result = "";
        foreach($list as $key => $val) {
            $id = $name . "_" . $key;
            $radio = $this->radio($name, $key, $checked, array_merge($options, array('id' => $id)));
            $label = $this->label($id, $val);
            if($block instanceof \Closure) {
                $result .= $block($radio, $label);
            } else {
                $result .= $radio . $label;
            }
        }

        return $result;
    }

    public function errorMessagesFor($propertyID, \Closure $block = null) {
        $result = null;

        if(!is_null($this->result) && array_key_exists($propertyID, $this->result['ERRORS'])) {
            if(!is_array($this->result['ERRORS'][$propertyID])) {
                $this->result['ERRORS'][$propertyID] = array($this->result['ERRORS'][$propertyID]);
            }

            $formatter = is_null($block) ? $this->errorMessagesFormatCallback : $block;
            $formattedMessages = $this->formatErrorMessages($this->result['ERRORS'][$propertyID], $formatter);
            $result = $this->errorMessagesWrapper->setContent($formattedMessages);
        }

        return (string)$result;
    }

    protected function formatErrorMessages($messages, $formatter) {
        $result = "";
        foreach($messages as $message) {
            $result .= call_user_func_array($formatter, array($message));
        }

        return $result;
    }

    public function setErrorMessagesFormatCallback($formatCallback) {
        if(is_callable($formatCallback)) $this->errorMessagesFormatCallback = $formatCallback;
    }
}
