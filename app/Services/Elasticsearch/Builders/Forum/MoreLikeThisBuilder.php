<?php

namespace Coyote\Services\Elasticsearch\Builders\Forum;

use Coyote\Services\Elasticsearch\Filters\Post\ForumMustExist;
use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Topic;

class MoreLikeThisBuilder
{
    /**
     * @param Topic $topic
     * @param int|array $forumId
     * @return QueryBuilderInterface
     */
    public function build(Topic $topic, $forumId) : QueryBuilderInterface
    {
        $builder = new QueryBuilder();

        $mlt = new MoreLikeThis(['subject', 'posts.text']);
        $mlt->addDoc([
            '_index'    => config('elasticsearch.default_index'),
            '_type'     => 'topics',
            '_id'       => $topic->id
        ]);

        $builder->must($mlt);

        $builder->must(new OnlyThoseWithAccess($forumId));
        $builder->must(new ForumMustExist('id', $topic->id));

        return $builder;
    }
}
