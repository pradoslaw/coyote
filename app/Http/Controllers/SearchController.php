<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Factory;
use Coyote\Services\Elasticsearch\SearchOptions;
use Coyote\Services\Forum\TreeBuilder\JsonDecorator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Coyote\Services\Forum\TreeBuilder\Builder as TreeBuilder;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    const PAGE_LIMIT = 1500;

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

        $payload = [
            'hits'              => null,
            'model'             => $request->input('model'),
            'query'             => $request->input('q'),
            'sort'              => $request->input('sort'),
            'user'              => $request->input('user'),
            'categories'        => $request->input('categories', []),
            'page'              => $request->input('page', 1),
            'posts_per_page'    => $this->getSetting('forum.posts_per_page', 10),
            'page_limit'        => self::PAGE_LIMIT,
            'forums'            => $forums
        ];

        try {
            $this->validate($request, [
                'q'         => 'nullable|string',
                'sort'      => 'nullable|in:' . SearchOptions::DATE . ',' . SearchOptions::SCORE,
                'page'      => 'nullable|integer|min:1|max:' . self::PAGE_LIMIT
            ]);

            $payload['hits'] = $strategy->search($request)->content();

            if ($request->wantsJson()) {
                return $payload['hits'];
            }
        } catch (ConnectException $exception) {
            $this->handleException($payload, 500, 'Brak połączenia z serwerem wyszukiwarki.');
        } catch (ServerException | ClientException $exception) {
            $this->handleException($payload, 500, 'Serwer wyszukiwarki nie może przetworzyć tego żądania.');
        } catch (ValidationException $exception) {
            $this->handleException($payload, 422, $exception->getMessage());
        }

        return $this->view('search', $payload);
    }

    private function handleException(array &$payload, int $code, string $message)
    {
        if ($this->request->wantsJson()) {
            abort($code, $message);
        }

        $payload['error'] = $message;
    }

    private function formatArray(Request $request)
    {
        if (is_string($request->input('categories'))) {
            $value = array_filter(array_map('intval', explode(',', $request->input('categories'))));

            $request->replace(array_merge($request->all(), ['categories' => $value]));
        }
    }
}
