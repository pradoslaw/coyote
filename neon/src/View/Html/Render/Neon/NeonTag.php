<?php
namespace Neon\View\Html\Render\Neon;

interface NeonTag extends \Neon\View\Html\Tag
{
    public function html(): string;
}
