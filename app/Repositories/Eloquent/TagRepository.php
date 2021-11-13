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
            ->whereNotNull('logo')
            ->groupBy('name')
            ->groupBy('logo')
            ->groupBy('resources')
            ->orderByRaw("COALESCE(resources ->> '" . Job::class . "', '0')::int DESC")
            ->limit(5)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function tagClouds(string $model): array
    {
        return $this
            ->model
            ->select(['id', 'name', 'logo'])
            ->addSelect($this->raw("COALESCE(resources ->> '$model', '0')::int AS count"))
            ->orderByRaw('count DESC')
            ->limit(10)
            ->get()
            ->pluck('count', 'name')
            ->toArray();
    }

    public function popularTags(string $model)
    {
        return $this
            ->model
            ->select(['tags.id', 'tags.name', 'real_name', 'logo', 'tag_categories.name AS category'])
            ->addSelect($this->raw("COALESCE(resources ->> '$model', '0')::int AS count"))
            ->leftJoin('tag_categories', 'tag_categories.id', '=', 'category_id')
            ->whereRaw("COALESCE(resources ->> '$model', '0')::int > 0")
            ->orderByRaw('count DESC')
            ->limit(25)
            ->get();
    }
}
