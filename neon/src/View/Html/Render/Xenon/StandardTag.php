<?php
namespace Neon\View\Html\Render\Xenon;

use Neon\View\Html\Tag;
use Xenon\ViewItem;

readonly class StandardTag extends \Xenon\Tag implements Tag, ViewItem
{
    public function __construct(private ?string $parentClass, string $tag, array $attributes, array $children)
    {
        parent::__construct($tag, $attributes, [], $children);
    }

    public function parentClass(): ?string
    {
        return $this->parentClass;
    }
}
