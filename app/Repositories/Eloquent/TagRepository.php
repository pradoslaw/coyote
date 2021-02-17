<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Job;
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

    public function exists(string $name): bool
    {
        return $this
            ->model
            ->where('name', $name)
            ->exists();
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
    public function categorizedTags(array $tags)
    {
        return $this
            ->model
            ->selectRaw('name, logo')
            ->whereIn('name', $tags)
            ->whereNotNull('category_id')
            ->groupBy('name')
            ->groupBy('logo')
            ->groupBy('resources')
            ->orderByRaw("COALESCE(resources ->> '" . Job::class . "', '0')::int DESC")
            ->limit(5)
            ->get();
    }
}
