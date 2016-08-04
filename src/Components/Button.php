<?php

namespace Boduch\Grid\Components;

class Button extends Component
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string $url
     * @param string $text
     * @param array $attributes
     */
    public function __construct($url, $text, array $attributes = [])
    {
        $this->url = $url;
        $this->text = $text;
        $this->setDefaultAttributes($attributes);
    }

    /**
     * @return \Illuminate\Support\HtmlString
     */
    public function render()
    {
        return $this->tag('a', $this->text, $this->attributes);
    }

    /**
     * @param array $attributes
     */
    protected function setDefaultAttributes(array $attributes = [])
    {
        $this->attributes = array_merge(['class' => 'btn btn-default', 'href' => $this->url], $attributes);
    }
}
