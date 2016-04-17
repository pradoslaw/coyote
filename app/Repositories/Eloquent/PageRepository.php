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
        return $this->model->select()->where('content_id', $id)->where('content_type', $content)->first();
    }
}
