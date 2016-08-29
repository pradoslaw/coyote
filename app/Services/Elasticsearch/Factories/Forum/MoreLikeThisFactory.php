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

        $mlt = new MoreLikeThis(['subject', 'post.text']);
        $mlt->addDoc([
            '_index' => config('elasticsearch.default_index'),
            '_type' => 'topics',
            '_id' => $topic->id
        ]);

        $builder->addMoreLikeThis($mlt);
//        $builder->addAggs(new Topic());

        $builder->addFilter(new Forum($forumId));
        $builder->addFilter(new NotTerm('id', $topic->id));

        return $builder;
    }
}
