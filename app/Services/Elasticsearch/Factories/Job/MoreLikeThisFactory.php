<?php

namespace Coyote\Services\Elasticsearch\Factories\Job;

use Coyote\Services\Elasticsearch\Filters\NotTerm;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class MoreLikeThisFactory
{
    /**
     * @param \Coyote\Job $job
     * @return QueryBuilderInterface
     */
    public function build($job) : QueryBuilderInterface
    {
        $builder = new QueryBuilder();

        $mlt = new MoreLikeThis(['title', 'description', 'tags']);
        $mlt->addDoc([
            '_index' => config('elasticsearch.default_index'),
            '_type' => 'jobs',
            '_id' => $job->id
        ]);

        $builder->addMoreLikeThis($mlt);
        $builder->addFilter(new NotTerm('id', $job->id));

        $builder->setSize(0, 10);

        return $builder;
    }
}
