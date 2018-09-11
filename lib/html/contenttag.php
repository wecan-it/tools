<?php namespace Wecan\Tools\Html;

class ContentTag
{
    protected $type;
    protected $content;
    protected $options;

    public function __construct($type, $content = "", $options = array(), $block = null) {
        $args = func_get_args();
        if(count($args) == 2) {
            list($type, $options) = $args;
        }
        $this->type = $type;
        $this->content = ($block instanceof \Closure) ? $block() : $content;
        $this->setOptions($options);
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @return mixed
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    public function addContent($content) {
        $this->content .= $content;

        return $this;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options) {
        $html = array();

        foreach((array)$options as $key => $value) {
            $element = $this->setOption($key, $value);
            if(!is_null($element)) $html[] = $element;
        }

        $this->options = count($html) > 0 ? ' ' . implode(' ', $html) : '';

        return $this;
    }

    protected function setOption($key, $value) {
        if(is_numeric($key)) $key = $value;

        if(!is_null($value)) return $key . '="' . htmlentities($value, ENT_QUOTES, 'UTF-8', false) . '"';

        return null;
    }

    public function __toString() {
        return '<' . $this->type . $this->options . '>' . $this->content . '</' . $this->type . '>';
    }
}