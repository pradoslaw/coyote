<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Job;
use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;

class MoreLikeThisBuilder extends QueryBuilder
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * @return array
     */
    public function build()
    {
        $mlt = new MoreLikeThis(['title', 'description', 'tags']);
        $mlt->addDoc([
            '_index'        => config('elasticsearch.default_index'),
            '_type'         => '_doc',
            '_id'           => "job_{$this->job->id}"
        ]);

        $this->must($mlt);
        $this->mustNot(new Term('id', $this->job->id));

        $this->size(0, 5);

        return parent::build();
    }
}
