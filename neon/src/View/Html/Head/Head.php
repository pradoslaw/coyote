<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;

interface Head
{
    public function headHtml(Render $h): string;
}
