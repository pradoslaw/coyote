<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PageRepositoryInterface;

class PageRepository extends Repository implements PageRepositoryInterface
{
    /**
     * @return \Coyote\Page
     */
    public function model()
    {
        return 'Coyote\Page';
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function findByPath($path)
    {
        return $this->model->select()->whereRaw('LOWER(path) = ?', [mb_strtolower($path)])->first();
    }

    /**
     * @param $id
     * @param $content
     * @return mixed
     */
    public function findByContent($id, $content)
    {
        // we use firstOrNew() because we don't want to return NULL
        return $this
            ->model
            ->select()
            ->where('content_id', $id)
            ->where('content_type', $content)
            ->firstOrNew(['id' => null]);
    }

    /**
     * Build query for sitemap.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function forSitemap()
    {
        return $this->model->where('allow_sitemap', 1);
    }

    /**
     * @param int $pageId
     * @return mixed
     */
    public function visits($pageId)
    {
        return $this
            ->model
            ->select(['page_visits.*', 'users.name AS user_name', 'users.is_active', 'users.is_blocked'])
            ->join('page_visits', 'page_visits.page_id', '=', 'pages.id')
            ->join('users', 'users.id', '=', 'user_id')
            ->where('pages.id', $pageId)
            ->get();
    }
}
