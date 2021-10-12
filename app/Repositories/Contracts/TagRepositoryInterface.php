<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Tag;
use JetBrains\PhpStorm\ArrayShape;

interface TagRepositoryInterface extends RepositoryInterface
{
    public function exists(string $name): bool;

    /**
     * @param string[] $tags
     * @return int[] Ids of tags
     */
    public function multiInsert(array $tags);

    /**
     * @param array $tags
     * @return Tag[]
     */
    public function categorizedTags(array $tags);

    /**
     * @param string $model
     * @return array
     */
    public function tagClouds(string $model): array;

    /**
     * @param string $model
     * @return \Coyote\Tag[]
     */
    public function popularTags(string $model);
}
