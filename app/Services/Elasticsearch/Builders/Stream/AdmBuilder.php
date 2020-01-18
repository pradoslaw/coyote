<?php

namespace Coyote\Services\Elasticsearch\Builders\Stream;

use Coyote\Services\Elasticsearch\Filters\Term;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\Sort;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdmBuilder extends QueryBuilder
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
        $this->buildSort();
        $this->buildSize();

        $this->must(new Term('model', 'stream'));

        if ($this->request->filled('text')) {
            $this->must(new MultiMatch($this->request->input('text'), []));
        }

        foreach (['ip', 'browser', 'fingerprint'] as $inputKey) {
            if ($this->request->filled($inputKey)) {
                $this->must(new Term($inputKey, $this->request->input($inputKey)));
            }
        }

        if ($this->request->filled('actor_displayName')) {
            $this->must(new Term('actor.displayName', Str::lower($this->request->input('actor_displayName'))));
        }

        return parent::build();
    }

    protected function buildSort()
    {
        $this->sort(new Sort('created_at', 'desc'));
    }

    protected function buildSize()
    {
        $this->size(self::PER_PAGE * (max(0, (int) $this->request->get('page', 1) - 1)), self::PER_PAGE + 1);
    }
}
