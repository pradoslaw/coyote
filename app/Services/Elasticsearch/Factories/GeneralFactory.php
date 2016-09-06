<?php

namespace Coyote\Services\Elasticsearch\Factories;

use Coyote\Services\Elasticsearch\Query;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\Highlight;
use Illuminate\Http\Request;

class GeneralFactory
{
    /**
     * @param Request $request
     * @return QueryBuilderInterface
     */
    public function build(Request $request) : QueryBuilderInterface
    {
        $builder = new QueryBuilder();
        $builder->addQuery(new Query($request->input('q'), []));
        $builder->addSort(new Sort($request->get('sort', '_score'), $request->get('order', 'desc')));
        $builder->addHighlight(new Highlight(['subject', 'text', 'title']));

        $builder->setSize(($request->input('page', 1) - 1) * 10, 10);

        return $builder;
    }
}
