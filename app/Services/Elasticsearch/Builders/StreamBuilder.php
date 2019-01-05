<?php

namespace Coyote\Services\Elasticsearch\Builders;

use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\Sort;
use Illuminate\Http\Request;

class StreamBuilder extends QueryBuilder
{
    const PER_PAGE = 20;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->sort(new Sort('created_at', 'desc'));
        $this->size(self::PER_PAGE * (max(0, (int) $this->request->get('page', 1) - 1)), self::PER_PAGE + 1);

        foreach (['ip', 'browser', 'fingerprint'] as $inputKey) {
            if ($this->request->filled($inputKey)) {
                $this->must(new Term($inputKey, $this->request->input($inputKey)));
            }
        }

        if ($this->request->filled('actor_displayName')) {
            $this->must(new Term('actor.displayName', $this->request->input('actor_displayName')));
        }

        return parent::build();
    }
}
