<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Elasticsearch\Filters\Post\Forum;
use Coyote\Elasticsearch\Filters\Term;
use Coyote\Elasticsearch\Highlight;
use Coyote\Elasticsearch\Query;
use Coyote\Elasticsearch\QueryBuilderInterface as QueryBuilder;
use Coyote\Elasticsearch\QueryParser;
use Coyote\Elasticsearch\Sort;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    const FIELD_IP = 'ip';
    const FIELD_USER = 'user';
    const FIELD_BROWSER = 'browser';
    const FIELD_HOST = 'host';

    /**
     * @param Request $request
     * @param Post $post
     * @param User $user
     * @param QueryBuilder $queryBuilder
     * @return mixed
     */
    public function index(Request $request, Post $post, User $user, QueryBuilder $queryBuilder)
    {
        $this->breadcrumb->push('Szukaj', route('forum.search'));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList('id'); // forum id as key

        $users = [];
        $response = $highlights = null;

        if ($request->has('q')) {
            $forumsId = array_keys($forumList);
            // we have to make sure user is not trying to search in category without access
            $this->validate($request, ['f' => 'sometimes|int|in:' . implode(',', $forumsId)]);

            // we need to limit results to given categories...
            $filterForum = new Forum($request->has('f') ? $request->get('f') : $forumsId);
            $queryBuilder->addFilter($filterForum);

            // parse given query and fetch keywords and filters
            $parser = new QueryParser(
                $request->get('q'),
                [self::FIELD_IP, self::FIELD_USER, self::FIELD_BROWSER, self::FIELD_HOST]
            );

            // only for see returned values in debugbar
            debugbar()->debug(
                $parser->getFilteredQuery(),
                $parser->getFilters()
            );

            $queryBuilder->addSort(new Sort($request->get('sort', '_score'), $request->get('order', 'desc')));
            $queryBuilder->addHighlight(new Highlight(['topic.subject', 'text', 'tags']));

            // we cannot allowed regular uesrs to search by IP or host
            foreach ([self::FIELD_IP, self::FIELD_HOST, self::FIELD_BROWSER] as $filter) {
                if (!$request->user()->can('forum-update')) {
                    $parser->removeFilter($filter); // user is not ALLOWED to use this filter
                }
            }

            if ($parser->getFilter(self::FIELD_USER)) {
                $value = mb_strtolower($parser->pullFilter(self::FIELD_USER));
                $result = $user->findByName($value);

                if ($result) {
                    $field = 'user_id';
                    $value = $result->id;
                } else {
                    $field = 'user_name';
                }

                $queryBuilder->addFilter(new Term($field, $value));
            }

            // filter by browser is not part of the filter. we need to append it to query
            $parser->appendQuery(['browser' => $parser->pullFilter(self::FIELD_BROWSER)]);

            // we need to apply rest of the filters
            foreach ($parser->getFilters() as $field => $value) {
                $queryBuilder->addFilter(new Term($field, $value));
            }

            // specify query string and fields
            if ($parser->getFilteredQuery()) {
                $queryBuilder->addQuery(new Query($parser->getFilteredQuery(), ['text', 'topic.subject', 'tags']));
            }

            $build = $queryBuilder->build();
            debugbar()->debug($build);

            $response = $post->search($build);
            $highlights = $response->getHighlights();

            if ($response->totalHits() > 0) {
                $usersId = $response->keyBy('_source.user_id')->keys();
                $users = $user->whereIn('id', array_map('intval', $usersId->toArray()))->get()->keyBy('id');
            }

            $this->breadcrumb->push('Wyniki wyszukiwania', $request->fullUrl());
        }

        return $this->view('forum.search')->with(compact('forumList', 'users', 'response', 'highlights'));
    }
}
