<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Block as Model;

class Block extends ObjectAbstract
{
    /**
     * @var string
     */
    public $content;

    /**
     * @param Model $block
     * @return $this
     */
    public function map(Model $block)
    {
        $this->id = $block->id;
        $this->url = route('adm.blocks.save', [$block->id], false);
        $this->displayName = $block->name;
        // block content is most likely html code. do not strip html code before saving. we do that before showing
        // this fragment to user.
        $this->content = str_limit($block->content, 200);

        return $this;
    }
}
