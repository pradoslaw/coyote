<?php
namespace Neon\View\Head;

interface Head
{
    public function headHtml(callable $h): string;
}
