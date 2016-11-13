<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Flag as Model;

class Flag extends ObjectAbstract
{
    /**
     * @param Model $flag
     * @return $this
     */
    public function map(Model $flag)
    {
        $this->id = $flag->id;
        $this->displayName = excerpt(htmlspecialchars($flag->text));
        $this->url = $flag->url;

        return $this;
    }
}
