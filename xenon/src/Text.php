<?php
namespace Xenon;

class Text implements ViewItem
{
    public function __construct(public string $text)
    {
    }
}
