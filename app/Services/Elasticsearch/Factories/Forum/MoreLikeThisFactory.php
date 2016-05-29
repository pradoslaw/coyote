<?php

namespace Coyote\Services\Elasticsearch\Factories\Forum;

use Coyote\Services\Elasticsearch\Aggs\Forum\Topic;
use Coyote\Services\Elasticsearch\Filters\NotTerm;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\Filters\Post\Forum;

class MoreLikeThisFactory
{
    /**
     * @param \Coyote\Topic $topic
     * @param int|array $forumId
     * @return QueryBuilderInterface
     */
    public function build($topic, $forumId) : QueryBuilderInterface
    {
        $builder = new QueryBuilder();

        $mlt = new MoreLikeThis(['subject', 'text']);
        $mlt->addDoc([
            '_index' => config('elasticsearch.default_index'),
            '_type' => 'posts',
            '_id' => $topic->first_post_id
        ]);

        $builder->addMoreLikeThis($mlt);
        $builder->addAggs(new Topic());

        $builder->addFilter(new Forum($forumId));
        $builder->addFilter(new NotTerm('topic_id', $topic->id));

        $builder->setSize(0, 10);

        return $builder;
    }
}
