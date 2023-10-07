<?php
namespace Coyote\View\Twig;

use Coyote\Domain\Html;

class TwigLiteral
{
    public function __construct(private string $content)
    {
    }

    public static function fromHtml(Html $html): self
    {
        return new self($html->content);
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
