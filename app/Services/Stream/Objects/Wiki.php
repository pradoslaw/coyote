<?php

namespace Coyote\Services\Stream\Objects;

use Coyote\Services\UrlBuilder;
use Coyote\Wiki as Model;

class Wiki extends ObjectAbstract
{
    /**
     * @var string|null
     */
    public $excerpt;

    /**
     * @param Model $wiki
     * @return $this
     */
    public function map(Model $wiki)
    {
        $this->id = $wiki->id;
        $this->url = UrlBuilder::wiki($wiki);
        $this->displayName = $wiki->title;

        if ($wiki->excerpt) {
            $this->excerpt = $wiki->excerpt;
        }

        return $this;
    }
}
