<?php

namespace Coyote\Services\Elasticsearch\Builders\Forum;

use Coyote\Http\Factories\GateFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Functions\Decay;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\QueryParser;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\Highlight;
use Coyote\Services\Elasticsearch\Filters\Term;
use Illuminate\Http\Request;

class SearchBuilder
{
    const FIELD_IP          = 'ip';
    const FIELD_USER        = 'user';
    const FIELD_BROWSER     = 'browser';
    const FIELD_HOST        = 'host';

    use GateFactory;

    public function build(Request $request, $forumId) : QueryBuilderInterface
    {
        $builder = new QueryBuilder();
        $builder->must(new OnlyThoseWithAccess($forumId));
        $builder->sort(new Sort($request->get('sort', '_score'), $request->get('order', 'desc')));
        $builder->highlight(new Highlight(['topic.subject', 'text', 'tags']));

        // parse given query and fetch keywords and filters
        $parser = new QueryParser(
            $request->get('q'),
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

            $builder->must(new Term($field, $value));
        }

        // filter by browser is not part of the filter. we need to append it to query
        $parser->appendQuery(['browser' => $parser->pullFilter(self::FIELD_BROWSER)]);

        // we need to apply rest of the filters
        foreach ($parser->getFilters() as $field => $value) {
            $builder->must(new Term($field, $value));
        }

        // specify query string and fields
        if ($parser->getFilteredQuery()) {
            $builder->must(new MultiMatch($parser->getFilteredQuery(), ['text^2', 'topic.subject', 'tags^4']));
        }

        $builder->scoreFunction(new Decay('created_at', '180d'));
        $builder->size(($request->input('page', 1) - 1) * 10, 10);

        return $builder;
    }
}
