<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Group as Model;

class Group extends Object
{
    /**
     * @param Model $group
     * @return $this
     */
    public function map(Model $group)
    {
        $this->id = $group->id;
        $this->url = route('adm.groups.save', [$group->id], false);
        $this->displayName = $group->name;

        return $this;
    }
}
