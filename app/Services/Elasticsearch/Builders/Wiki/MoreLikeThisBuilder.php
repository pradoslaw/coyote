<?php

namespace Coyote\Services\Elasticsearch\Builders\Wiki;

use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Wiki;

class MoreLikeThisBuilder
{
    /**
     * @param Wiki $wiki
     * @return QueryBuilderInterface
     */
    public function build(Wiki $wiki) : QueryBuilderInterface
    {
        $builder = new QueryBuilder();

        $mlt = new MoreLikeThis(['title', 'excerpt']);
        $mlt->addDoc([
            '_index'    => config('elasticsearch.default_index'),
            '_type'     => 'wiki',
            '_id'       => $wiki->id
        ]);

        $builder->must($mlt);
        $builder->mustNot(new Term('id', $wiki->id));
        $builder->mustNot(new Term('wiki_id', $wiki->wiki_id));

        $builder->size(0, 10);

        return $builder;
    }
}
