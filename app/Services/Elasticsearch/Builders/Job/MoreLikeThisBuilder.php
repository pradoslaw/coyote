<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Job;
use Coyote\Services\Elasticsearch\Filters\Term;
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
            '_index'        => config('elasticsearch.default_index'),
            '_type'         => 'jobs',
            '_id'           => $job->id
        ]);

        $builder->must($mlt);
        $builder->mustNot(new Term('id', $job->id));

        $builder->size(0, 5);

        return $builder;
    }
}
