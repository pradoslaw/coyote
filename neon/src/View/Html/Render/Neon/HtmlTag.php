<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Tag;

readonly class HtmlTag implements Tag
{
    public ?string $parentClass;

    public function __construct(private string $html)
    {
        $this->parentClass = null;
    }

    public function html(): string
    {
        return $this->html;
    }
}
