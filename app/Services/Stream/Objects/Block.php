<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Block as Model;

class Block extends Object
{
    /**
     * @param Model $block
     * @return $this
     */
    public function map(Model $block)
    {
        $this->id = $block->id;
        $this->url = route('adm.blocks.save', [$block->id], false);
        $this->displayName = $block->name;
        $this->content = str_limit(htmlspecialchars($block->content), 200);

        return $this;
    }
}
