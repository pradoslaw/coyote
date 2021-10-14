<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Guide as Model;
use Coyote\Services\UrlBuilder;

class Guide extends ObjectAbstract
{
    /**
     * @param Model $guide
     * @return $this
     */
    public function map(Model $guide)
    {
        $this->id = $guide->id;
        $this->url = UrlBuilder::guide($guide);
        $this->displayName = $guide->title;

        return $this;
    }
}
