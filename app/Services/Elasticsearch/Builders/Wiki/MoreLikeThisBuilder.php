<?php

namespace Coyote\Services\Elasticsearch\Builders\Wiki;

use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\MoreLikeThis;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Wiki;

class MoreLikeThisBuilder extends QueryBuilder
{
    /**
     * @var Wiki
     */
    private $wiki;

    /**
     * @param Wiki $wiki
     */
    public function __construct(Wiki $wiki)
    {
        $this->wiki = $wiki;
    }

    /**
     * @return array
     */
    public function build()
    {
        $mlt = new MoreLikeThis(['title', 'excerpt']);
        $mlt->addDoc([
            '_index'    => config('elasticsearch.default_index'),
            '_type'     => 'wiki',
            '_id'       => $this->wiki->id
        ]);

        $this->must($mlt);
        $this->mustNot(new Term('id', $this->wiki->id));
        $this->mustNot(new Term('wiki_id', $this->wiki->wiki_id));

        $this->size(0, 10);

        return parent::build();
    }
}
