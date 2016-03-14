<?php

namespace Coyote\Http\Controllers\Forum;

use Coyote\Repositories\Contracts\PostRepositoryInterface as Post;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    public function index(Request $request, Post $post, User $user)
    {
        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $result = [];
        $total = 0;
        $users = [];

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
                ]
            ];

            $response = $post->search($body);

            if (isset($response['hits']['hits'])) {
                $total = $response['hits']['total'];
                $result = collect($response['hits']['hits']);

                $usersId = $result->keyBy('_source.user_id')->keys();
                $users = $user->whereIn('id', array_map('intval', $usersId->toArray()))->get()->keyBy('id');
            }
        }

        return $this->view('forum.search')->with(compact('forumList', 'users', 'result', 'total'));
    }
}
