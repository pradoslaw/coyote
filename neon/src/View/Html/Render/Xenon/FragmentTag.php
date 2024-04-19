<?php
namespace Neon\View\Html\Render\Xenon;

use Neon\View\Html\Tag;
use Xenon\ViewItem;

readonly class FragmentTag extends \Xenon\Fragment implements Tag, ViewItem
{
    public function spaNode(): string
    {
        return $this->spaExpression();
    }

    public function parentClass(): ?string
    {
        return null;
    }
}
