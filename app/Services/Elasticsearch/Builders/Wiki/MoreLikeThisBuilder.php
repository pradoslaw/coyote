<?php

namespace Coyote\Services\Elasticsearch\Builders\Wiki;

use Coyote\Services\Elasticsearch\Filters\NotTerm;
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

        $mlt = new MoreLikeThis(['title', 'text', 'excerpt']);
        $mlt->addDoc([
            '_index' => config('elasticsearch.default_index'),
            '_type' => 'wiki',
            '_id' => $wiki->id
        ]);

        $builder->addMoreLikeThis($mlt);
        $builder->addFilter(new NotTerm('id', $wiki->id));
        $builder->addFilter(new NotTerm('wiki_id', $wiki->wiki_id));

        $builder->setSize(0, 10);

        return $builder;
    }
}
