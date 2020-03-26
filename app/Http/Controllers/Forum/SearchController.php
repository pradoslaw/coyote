<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Services\Elasticsearch\Builders\Forum\SearchBuilder;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Forum\TreeBuilder\Builder;
use Coyote\Services\Forum\TreeBuilder\ListDecorator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends BaseController
{
    const FIELD_IP          = 'ip';
    const FIELD_USER        = 'user';
    const FIELD_BROWSER     = 'browser';
    const FIELD_HOST        = 'host';

    /**
     * @param Request $request
     * @param UserRepository $user
     * @return mixed
     */
    public function index(Request $request, UserRepository $user)
    {
        $this->breadcrumb->push('Szukaj', route('forum.search'));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = (new ListDecorator(new Builder($this->forum->list())))->setKey('id')->build(); // forum id as key

        $users = [];
        $response = $highlights = $pagination = null;

        if ($request->filled('q')) {
            $forumsId = array_keys($forumList);
            // we have to make sure user is not trying to search in category without access
            $this->validate($request, [
                'f' => 'nullable|int|in:' . implode(',', $forumsId),
                'page' => 'nullable|int',
                'sort' => 'nullable|in:_score,id',
                'order' => 'nullable|in:asc,desc'
            ]);

            // we need to limit results to given categories...
            $builder = (new SearchBuilder($request, $request->filled('f') ? $request->get('f') : $forumsId));
            $response = $this->post->search($builder);

            $highlights = $response->getHighlights();

            if ($response->total() > 0) {
                $usersId = $response->keyBy('_source.user_id')->keys();

                $user->pushCriteria(new WithTrashed());
                $users = $user->findMany(array_map('intval', $usersId->toArray()))->keyBy('id');
            }

            $this->breadcrumb->push('Wyniki wyszukiwania', $request->fullUrl());

            $pagination = new LengthAwarePaginator($response, $response->total(), 10, null, ['path' => ' ']);
            $pagination->appends($request->except('page'));
        }

        return $this->view('forum.search')->with(compact('forumList', 'users', 'response', 'highlights', 'pagination'));
    }
}
