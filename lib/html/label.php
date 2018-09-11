<?php namespace Wecan\Tools\Html;

class Label extends ContentTag
{
    protected $name;

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    public function __construct($name, $content, $options, $block = null) {
        $this->type = 'label';
        $this->name = $name;
        $this->content = ($block instanceof \Closure) ? $block() : $content;
        $this->setOptions($options);
    }

    public function __toString() {
        return '<label for="'.$this->name.'"' . $this->options . '>' . $this->content . '</label>';
    }
}