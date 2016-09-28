<?php

namespace Coyote\Services\Elasticsearch\Factories;

use Coyote\Services\Elasticsearch\Filters\Post\Forum;
use Coyote\Services\Elasticsearch\Query;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\Highlight;
use Illuminate\Http\Request;

class MixedFactory
{
    const PER_PAGE = 10;

    /**
     * @param Request $request
     * @return QueryBuilderInterface
     */
    public function build(Request $request) : QueryBuilderInterface
    {
        $fields = [
            'topics.subject',
            'topics.text',
            'microblogs.text',
            'wiki.title',
            'wiki.long_title',
            'wiki.text',
            'wiki.excerpt',
            'jobs.title',
            'jobs.description',
            'jobs.requirements',
            'jobs.recruitment'
        ];

        $builder = new QueryBuilder();
        $builder->addQuery(new Query($request->input('q'), $fields));
        $builder->addSort(new Sort($request->get('sort', '_score'), $request->get('order', 'desc')));
        $builder->addHighlight(new Highlight(['subject', 'text', 'title', 'long_title', 'excerpt', 'description', 'requirements']));
        $builder->addFilter(new Forum($request->attributes->get('forum_id')));

        $builder->setSize(($request->input('page', 1) - 1) * self::PER_PAGE, self::PER_PAGE);

        return $builder;
    }
}
