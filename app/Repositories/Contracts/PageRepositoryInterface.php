<?php

namespace Coyote\Repositories\Contracts;

interface PageRepositoryInterface extends RepositoryInterface
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

    /**
     * @param int $pageId
     * @return mixed
     */
    public function visits($pageId);
}
