<?php
namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Node\Block\AbstractBlock;

class EmojiNode extends AbstractBlock
{
    public function __construct(public string $code)
    {
        parent::__construct();
    }
}
