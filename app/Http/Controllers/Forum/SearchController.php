<?php

namespace Coyote\Http\Controllers\Forum;

use Illuminate\Http\Request;
use Elasticsearch\ClientBuilder;

class SearchController extends BaseController
{
    public function index(Request $request)
    {
        // create forum list for current user (according to user's privileges)
        $this->pushForumCriteria();
        $forumList = $this->forum->forumList();

        $result = [];
        $total = 0;

        if ($request->has('q')) {
            $params = [
                'index' => 'coyote',
                'type' => 'posts',
                'body' => [
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
                ]
            ];

            $client = app('Elasticsearch');
            $response = $client->search($params);

            if (isset($response['hits']['hits'])) {
                $total = $response['hits']['total'];
                $result = $response['hits']['hits'];
            }
        }

        return $this->view('forum.search')->with(compact('forumList', 'result', 'total'));
    }
}
