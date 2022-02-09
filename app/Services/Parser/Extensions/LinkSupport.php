<?php

namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Inline\Text;

trait LinkSupport
{
    protected function linkHasLabel(Link $link): bool
    {
        return $link->firstChild() instanceof Text && $link->firstChild()?->getLiteral() !== $link->getUrl();
    }

    protected function isValidLink(array | bool $components): bool
    {
        return $components !== false
            && array_key_exists('host', $components)
                && array_key_exists('path', $components)
                    && trim($components['path'], '/') !== '';
    }
}
