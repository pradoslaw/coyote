<?php

namespace Coyote;

use Coyote\Repositories\Contracts\TagRepositoryInterface;

trait Taggable
{
    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags()->sync(app(TagRepositoryInterface::class)->multiInsert($tags));
    }

    /**
     * @return array
     */
    public function getTagNames()
    {
        return $this->tags->pluck('name')->toArray();
    }
}
