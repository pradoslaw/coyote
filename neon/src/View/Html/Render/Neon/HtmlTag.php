<?php
namespace Neon\View\Html\Render\Neon;

readonly class HtmlTag implements NeonTag
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
