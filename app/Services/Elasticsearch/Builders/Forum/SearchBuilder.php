<?php

namespace Coyote\Services\Elasticsearch\Builders\Forum;

use Coyote\Http\Factories\GateFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Functions\Decay;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryParser;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\Highlight;
use Coyote\Services\Elasticsearch\Filters\Term;
use Illuminate\Http\Request;

class SearchBuilder extends QueryBuilder
{
    const FIELD_IP          = 'ip';
    const FIELD_USER        = 'user';
    const FIELD_BROWSER     = 'browser';
    const FIELD_HOST        = 'host';

    use GateFactory;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $forumsId;

    /**
     * @param Request $request
     * @param array $forumsId
     */
    public function __construct(Request $request, $forumsId)
    {
        $this->request = $request;
        $this->forumsId = $forumsId;
    }

    /**
     * @return array
     */
    public function build()
    {
        $this->must(new Term('model', 'post'));
        $this->must(new OnlyThoseWithAccess($this->forumsId));
        $this->sort(new Sort($this->request->get('sort', '_score'), $this->request->get('order', 'desc')));
        $this->highlight(new Highlight(['topic.subject', 'text', 'tags']));

        // parse given query and fetch keywords and filters
        $parser = new QueryParser(
            $this->request->get('q'),
            [self::FIELD_IP, self::FIELD_USER, self::FIELD_BROWSER, self::FIELD_HOST]
        );

        // we cannot allowed regular uesrs to search by IP or host
        foreach ([self::FIELD_IP, self::FIELD_HOST, self::FIELD_BROWSER] as $filter) {
            if (!$this->getGateFactory()->allows('forum-update')) {
                $parser->removeFilter($filter); // user is not ALLOWED to use this filter
            }
        }

        if ($parser->getFilter(self::FIELD_USER)) {
            $user = app(UserRepositoryInterface::class);

            $value = mb_strtolower($parser->pullFilter(self::FIELD_USER));
            $result = $user->findByName($value);

            if ($result) {
                $field = 'user_id';
                $value = $result->id;
            } else {
                $field = 'user_name';
            }

            $this->must(new Term($field, $value));
        }

        // filter by browser is not part of the filter. we need to append it to query
        $parser->appendQuery(['browser' => $parser->pullFilter(self::FIELD_BROWSER)]);

        // we need to apply rest of the filters
        foreach ($parser->getFilters() as $field => $value) {
            $this->must(new Term($field, $value));
        }

        // specify query string and fields
        if ($parser->getFilteredQuery()) {
            $this->must(new MultiMatch($parser->getFilteredQuery(), ['text^2', 'topic.subject', 'tags^4']));
        }

        $this->score(new Decay('created_at', '180d'));
        $this->size(($this->request->input('page', 1) - 1) * 10, 10);

        return parent::build();
    }
}
