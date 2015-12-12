<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\ForumRepositoryInterface as Forum;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    use Base;

    /**
     * @var Forum
     */
    private $forum;

    /**
     * @param Forum $forum
     */
    public function __construct(Forum $forum)
    {
        parent::__construct();

        $this->forum = $forum;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        $this->breadcrumb->push('Forum', route('forum.home'));
        $this->breadcrumb->push('Python', '/Forum/Python');
        $this->breadcrumb->push('Python - wybór "najlepszego" GUI cross-platform', '/Forum/Python/Test');

        $viewers = app('Session\Viewers');

        return parent::view('forum.topic')->with('viewers', $viewers->render($request->getRequestUri()));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function submit($forum)
    {
        $this->breadcrumb($forum);
        $this->breadcrumb->push('Nowy wątek', route('forum.topic.submit', [$forum->path]));

        return parent::view('forum.submit');
    }

    public function save($forum)
    {
    }
}
