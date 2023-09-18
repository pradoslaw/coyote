<?php
namespace Coyote\View\Twig;

use Coyote\Domain\Html;

class TwigLiteral
{
    public function __construct(private Html $html)
    {
    }

    public function __toString(): string
    {
        return $this->html->content;
    }
}
