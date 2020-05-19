<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Factory;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Illuminate\Http\Request;
use Coyote\Services\Forum\TreeBuilder\Builder as TreeBuilder;

class SearchController extends Controller
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @param ForumRepository $forum
     */
    public function __construct(ForumRepository $forum)
    {
        parent::__construct();

        $this->forum = $forum;

        $this->breadcrumb->push('Szukaj', route('search'));
    }

    /**
     * @param Request $request
     * @param Factory $factory
     * @return \Illuminate\View\View|string
     */
    public function index(Request $request, Factory $factory)
    {
        $this->forum->pushCriteria(new OnlyThoseWithAccess($this->auth));
        $this->forum->pushCriteria(new AccordingToUserOrder($this->userId, true));

        $categories = (new ListDecorator(new TreeBuilder($this->forum->list())))->setKey('id')->build();
        $strategy = $factory->make($request->input('model'));

        try {
            $hits = $strategy->search($request)->content();

            if ($request->wantsJson()) {
                return $hits;
            }
        } catch (\Exception $e) {

        }

        return $this->view('search', [
            'hits'              => $hits ?? null,
            'model'             => $request->input('model'),
            'posts_per_page'    => $this->getSetting('forum.posts_per_page', 10),
            'categories'        => json_encode($categories)
        ]);
    }
}
