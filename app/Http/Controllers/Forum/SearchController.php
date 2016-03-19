<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Elasticsearch\Filters\Post\Forum;
use Coyote\Elasticsearch\Highlight;
use Coyote\Elasticsearch\Query;
use Coyote\Elasticsearch\QueryBuilderInterface as QueryBuilder;
use Coyote\Elasticsearch\Sort;
use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
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

            // specify query string and fields
            $queryBuilder->addQuery(new Query($request->get('q'), ['text', 'topic.subject', 'tags']));
            $queryBuilder->addSort(new Sort($request->get('sort', '_score'), $request->get('order', 'desc')));
            $queryBuilder->addHighlight(new Highlight(['topic.subject', 'text', 'tags']));

            $response = $post->search($queryBuilder->build());
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
