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
     * @inheritdoc
     */
    public function lookupName($name)
    {
        return $this
            ->model
            ->select(['tags.id', 'name', 'topics'])
            ->where('name', 'ILIKE', $name . '%')
            ->limit(100)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function countTopics(array $ids): void
    {
        $sql = 'UPDATE tags
                SET topics = (
                    SELECT COUNT(*)
                    FROM topic_tags
                             JOIN topics ON topics.id = topic_tags.topic_id
                    WHERE topic_tags.tag_id = tags.id AND topics.deleted_at IS NULL
                )
                WHERE tags.id in (%s)';

        $this->app['db']->update(sprintf($sql, implode(',', $ids)));
    }

    /**
     * @inheritdoc
     */
    public function multiInsert(array $tags)
    {
        $ids = [];

        foreach ($tags as $name) {
            $tag = $this->model->firstOrCreate(['name' => $name]);

            $ids[] = $tag->id;
        }

        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function getCategorizedTags(array $tags)
    {
        return $this
            ->model
            ->selectRaw('name, logo, COUNT(*) AS weight')
                ->join('job_tags', 'tag_id', '=', 'tags.id')
            ->whereIn('name', $tags)
            ->whereNotNull('category_id')
            ->groupBy('name')
            ->groupBy('logo')
            ->get();
    }
}
