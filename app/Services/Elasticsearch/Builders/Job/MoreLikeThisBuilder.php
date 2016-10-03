<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Job;
use Coyote\Services\Elasticsearch\Filters\NotTerm;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class MoreLikeThisBuilder
{
    /**
     * @param Job $job
     * @return QueryBuilderInterface
     */
    public function build(Job $job) : QueryBuilderInterface
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

        $builder->setSize(0, 5);

        return $builder;
    }
}
