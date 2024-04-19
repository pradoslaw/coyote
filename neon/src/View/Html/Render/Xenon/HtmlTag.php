<?php
namespace Neon\View\Html\Render\Xenon;

use Neon\View\Html\Tag;

readonly class HtmlTag extends \Xenon\Html implements Tag
{
    public function __construct(string $html)
    {
        parent::__construct('span', $html);
    }

    public function parentClass(): ?string
    {
        return null;
    }
}
