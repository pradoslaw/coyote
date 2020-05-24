<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Factory;
use Coyote\Services\Elasticsearch\SearchOptions;
use Coyote\Services\Forum\TreeBuilder\JsonDecorator;
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

        $forums = (new JsonDecorator(new TreeBuilder($this->forum->list())))->build();
        $strategy = $factory->make($request->input('model'));

        $this->formatArray($request);

        $response = [
            'hits'              => null,
            'model'             => $request->input('model'),
            'query'             => $request->input('q'),
            'sort'              => $request->input('sort'),
            'categories'        => $request->input('categories', []),
            'posts_per_page'    => $this->getSetting('forum.posts_per_page', 10),
            'forums'            => $forums
        ];

        try {
            $this->validate($request, [
                'q'         => 'nullable|string',
                'sort'      => 'nullable|in:' . SearchOptions::DATE . ',' . SearchOptions::SCORE
            ]);

            $response['hits'] = $strategy->search($request)->content();

            if ($request->wantsJson()) {
                return $response['hits'];
            }
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return $this->view('search', $response);
    }

    private function formatArray(Request $request)
    {
        if (is_string($request->input('categories'))) {
            $value = array_filter(array_map('intval', explode(',', $request->input('categories'))));

            $request->replace(array_merge($request->all(), ['categories' => $value]));
        }
    }
}
