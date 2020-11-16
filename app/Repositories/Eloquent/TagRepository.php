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
            ->orderByDesc('topics')
            ->limit(100)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function countTopics(array $ids = []): void
    {
        $sql = $this->app['db']->table('tags');

        $sql
            ->when(!empty($ids), function ($builder) use ($ids) {
                return $builder->whereIn('tags.id', $ids);
            })
            ->update([
                'topics' => $this->raw(
                    '(SELECT COUNT(*)
                    FROM topic_tags
                        JOIN topics ON topics.id = topic_tags.topic_id
                    WHERE topic_tags.tag_id = tags.id AND topics.deleted_at IS NULL)'
                ),

                'jobs' => $this->raw(
                    '(SELECT COUNT(*)
                    FROM job_tags
                        JOIN jobs ON jobs.id = job_tags.job_id
                    WHERE job_tags.tag_id = tags.id AND jobs.deleted_at IS NULL)'
                )
        ]);
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
