<?php

namespace Coyote\Services\Elasticsearch\Builders\Forum;

use Coyote\Services\Elasticsearch\Filters\Post\ForumMustExist;
use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Topic;

class MoreLikeThisBuilder extends QueryBuilder
{
    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var int
     */
    private $forumId;

    /**
     * @param Topic $topic
     * @param $forumId
     */
    public function __construct(Topic $topic, $forumId)
    {
        $this->topic = $topic;
        $this->forumId = $forumId;
    }

    /**
     * @return array
     */
    public function build()
    {
        $mlt = new MoreLikeThis(['subject', 'posts.text']);
        $mlt->addDoc([
            '_index'    => config('elasticsearch.default_index'),
            '_type'     => 'topics',
            '_id'       => $this->topic->id
        ]);

        $this->must($mlt);

        $this->must(new OnlyThoseWithAccess($this->forumId));
        $this->must(new ForumMustExist('id', $this->topic->id));

        return parent::build();
    }
}
