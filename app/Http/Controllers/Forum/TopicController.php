<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;

class TopicController extends Controller
{
    public function getIndex()
    {
        $this->breadcrumb->push('Forum', '/Forum');
        $this->breadcrumb->push('Python', '/Forum/Python');
        $this->breadcrumb->push('Python - wybór "najlepszego" GUI cross-platform', '/Forum/Python/Test');

        return parent::view('forum/topic');
    }

    public function postIndex()
    {
    }
}
