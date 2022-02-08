<?php

namespace Coyote\Services\Parser\Factories;

class SigFactory extends CommentFactory
{
    protected function getHtmlTags(): string
    {
        return implode(',', array_merge($this->htmlTags, ['br']));
    }
}
