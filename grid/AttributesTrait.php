<?php

namespace Boduch\Grid;

use Symfony\Component\HttpFoundation\ParameterBag;

trait AttributesTrait
{
    /**
     * @var ParameterBag
     */
    public $attributes;

    /**
     * @return ParameterBag
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->attributes->set($name, $value);
    }

    /**
     * @param $name
     * @return string|null
     */
    public function __get($name)
    {
        return $this->attributes->get($name);
    }
}
