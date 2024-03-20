<?php
namespace Neon\View;

interface Item
{
    /**
     * @return string[]
     */
    public function html(callable $h): array;
}
