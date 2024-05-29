<?php
namespace Coyote\Domain;

abstract class Html
{
    protected abstract function toHtml(): string;

    public function __toString(): string
    {
        return $this->toHtml();
    }
}
