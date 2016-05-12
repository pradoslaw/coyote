<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Wiki as Model;

class Wiki extends Object
{
    /**
     * @param Model $wiki
     * @return $this
     */
    public function map(Model $wiki)
    {
        $this->id = $wiki->id;
        $this->url = route('wiki.show', [$wiki->path], false);
        $this->displayName = excerpt($wiki->text);

        return $this;
    }
}
