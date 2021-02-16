<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Tag as Model;

class Tag extends ObjectAbstract
{
    /**
     * @param Model $tag
     * @return $this
     */
    public function map(Model $tag)
    {
        $this->id = $tag->id;
        $this->url = route('adm.tags.save', [$tag->id], false);
        $this->displayName = $tag->name;

        return $this;
    }
}
