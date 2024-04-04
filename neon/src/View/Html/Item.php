<?php
namespace Neon\View\Html;

interface Item
{
    /**
     * @return string[]
     */
    public function render(Render $h): array;
}
