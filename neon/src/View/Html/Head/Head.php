<?php
namespace Neon\View\Html\Head;

interface Head
{
    public function headHtml(callable $h): string;
}
