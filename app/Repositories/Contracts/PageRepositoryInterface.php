<?php

namespace Coyote\Repositories\Contracts;

interface PageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $path
     * @return \Coyote\Page
     */
    public function findByPath($path);

    /**
     * Build query for sitemap.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function forSitemap();

    /**
     * @param int $pageId
     * @return mixed
     */
    public function visits($pageId);
}
