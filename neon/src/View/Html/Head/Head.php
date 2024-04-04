<?php
namespace Neon\View\Html\Head;

use Neon\View\Html\Render;
use Neon\View\Html\Tag;

interface Head
{
    public function render(Render $h): Tag;
}
