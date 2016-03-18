<?php

namespace Coyote\Repositories\Contracts;

interface TagRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $name
     * @return mixed
     */
    public function lookupName($name);

    /**
     * @param array $tags
     * @return array Ids of tags
     */
    public function multiInsert(array $tags);
}
