<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PageRepositoryInterface;

class PageRepository extends Repository implements PageRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Page';
    }

    /**
     * @inheritdoc
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
    public function deleteByContent($id, $content)
    {
        var_dump($id,$content);
//        return $this
//            ->model
//            ->where('content_id', $id)
//            ->where('content_type', $content)
//            ->delete();
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
            ->select(['page_visits.*', 'users.name AS user_name', $this->raw('users.deleted_at IS NULL AS is_active'), 'users.is_blocked'])
            ->join('page_visits', 'page_visits.page_id', '=', 'pages.id')
            ->join('users', 'users.id', '=', 'user_id')
            ->where('pages.id', $pageId)
            ->get();
    }
}
