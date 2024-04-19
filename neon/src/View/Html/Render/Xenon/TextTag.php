<?php
namespace Neon\View\Html\Render\Xenon;

use Neon\View\Html\Tag;

readonly class TextTag extends \Xenon\Text implements Tag
{
    public function parentClass(): ?string
    {
        return null;
    }
}
