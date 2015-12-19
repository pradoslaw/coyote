<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\TagRepositoryInterface;

class TagRepository extends Repository implements TagRepositoryInterface
{
    /**
     * @return \Coyote\Tag
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
        return $this->model->select(['tags.id', 'name', \DB::raw('COUNT(topic_tags.id) AS count')])
                            ->leftJoin('topic_tags', 'topic_tags.tag_id', '=', 'tags.id')
                            ->where('name', 'ILIKE', $name . '%')
                            ->groupBy('tags.id')
                            ->get();
    }
}
