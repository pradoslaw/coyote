<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\TagRepositoryInterface;

class TagRepository extends Repository implements TagRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Tag';
    }

    /**
     * @param $name
     * @return mixed
     */
    public function lookupName($name)
    {
        return $this
            ->model
            ->select(['tags.id', 'name'])
            ->where('name', 'ILIKE', $name . '%')
            ->get();
    }

    /**
     * Insert tags and return theirs ids
     *
     * @param array $tags
     * @return array Ids of tags
     */
    public function multiInsert(array $tags)
    {
        $tagsId = [];

        foreach ($tags as $name) {
            $tag = $this->model->firstOrCreate(['name' => $name]);
            $tagsId[] = $tag->id;
        }

        return $tagsId;
    }
}
