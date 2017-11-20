<?php

namespace Coyote\Services\Elasticsearch\Builders\Job;

use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\Sort;

class FbBuilder extends QueryBuilder
{
    /**
     * @var string
     */
    protected $language;

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = strtolower($language);
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->must(new MultiMatch($this->language, ['title^3', 'tags.original^2']));
        $this->sort(new Sort('score', 'desc'));
        $this->size(0, 100);

        return parent::build();
    }
}
