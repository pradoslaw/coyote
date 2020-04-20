<?php

namespace Coyote\Services\Elasticsearch\Builders\Forum;

use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Filters\Term;
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
     * @var int[]
     */
    private $forumId;

    /**
     * @param Topic $topic
     * @param int[] $forumsId
     */
    public function __construct(Topic $topic, $forumsId)
    {
        $this->topic = $topic;
        $this->forumId = $forumsId;
    }

    /**
     * @return array
     */
    public function build()
    {
        $mlt = new MoreLikeThis(['subject', 'posts.text']);
        $mlt->addDoc([
            '_index'    => config('elasticsearch.default_index'),
            '_type'     => '_doc',
            '_id'       => "topic_{$this->topic->id}"
        ]);

        $this->must($mlt);
        $this->must(new Term('model', 'topic'));
        $this->must(new OnlyThoseWithAccess($this->forumId));
        // we need only those fields to save in cache
        $this->source(['id', 'subject', 'slug', 'updated_at', 'forum.*']);

        return parent::build();
    }
}
