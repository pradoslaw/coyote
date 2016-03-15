<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    public function index(Request $request, Post $post, User $user)
    {
        $this->breadcrumb->push('Szukaj', route('forum.search'));

        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $users = [];
        $response = null;

        if ($request->has('q')) {
            $body = [
                'query' => [
                    'query_string' => [
                        'query' => $request->get('q'),
                        'fields' => ['text', 'topic.subject', 'tags']
                    ]
                ],

                'highlight' => [
                    'pre_tags' => ['<em class="highlight">'],
                    'post_tags' => ["</em>"],
                    'fields' => [
                        'topic.subject' => (object) [],
                        'text' => (object) [],
                        'tags' => (object) []
                    ]
                ],

                'sort' => [
                    [$request->get('sort', '_score') => $request->get('order', 'desc')]
                ]
            ];

            $response = $post->search($body);

            if ($response->totalHits() > 0) {
                $usersId = $response->keyBy('_source.user_id')->keys();
                $users = $user->whereIn('id', array_map('intval', $usersId->toArray()))->get()->keyBy('id');
            }

            $this->breadcrumb->push('Wyniki wyszukiwania', $request->fullUrl());
        }

        return $this->view('forum.search')->with(compact('forumList', 'users', 'response'));
    }
}
