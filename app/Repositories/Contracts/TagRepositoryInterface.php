<?php

namespace Coyote\Repositories\Contracts;

interface TagRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $name
     * @return \Coyote\Tag[]
     */
    public function lookupName($name);

    /**
     * @param string[] $tags
     * @return int[] Ids of tags
     */
    public function multiInsert(array $tags);
}
