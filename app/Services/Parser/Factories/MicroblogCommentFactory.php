<?php

namespace Coyote\Services\Parser\Factories;

class MicroblogCommentFactory extends CommentFactory
{
    /**
     * @var bool
     */
    protected $enableHashParser = true;

    /**
     * @var bool
     */
    protected $enableLineBreaks = true;
}
