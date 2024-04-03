<?php
namespace Neon\View\Html;

interface Item
{
    /**
     * @return string[]
     */
    public function html(Render $h): array;
}
