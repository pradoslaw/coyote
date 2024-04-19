<?php
namespace Neon\View\Html\Render\Neon;

use Neon\View\Html\Tag;

readonly class HtmlTag implements Tag
{
    public function __construct(private string $html)
    {
    }

    public function html(): string
    {
        return $this->html;
    }

    public function parentClass(): ?string
    {
        return null;
    }
}
