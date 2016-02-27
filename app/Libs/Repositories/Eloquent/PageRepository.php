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
}
