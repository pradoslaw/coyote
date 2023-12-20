<?php
namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Node\Inline\AbstractInline;

class EmojiNode extends AbstractInline
{
    public function __construct(public string $code)
    {
        parent::__construct();
    }
}
