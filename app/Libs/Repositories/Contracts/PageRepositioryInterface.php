<?php

namespace Coyote\Repositories\Contracts;

interface PageRepositioryInterface extends RepositoryInterface
{
    /**
     * @param string $path
     * @return mixed
     */
    public function findByPath($path);

    /**
     * @param $id
     * @param $content
     * @return mixed
     */
    public function findByContent($id, $content);
}
