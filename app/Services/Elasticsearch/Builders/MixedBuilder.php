<?php

namespace Coyote\Services\Elasticsearch\Builders;

use Coyote\Services\Elasticsearch\Filters\Post\ForumMustExist;
use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\MultiMatch;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\Highlight;
use Illuminate\Http\Request;

class MixedBuilder
{
    const PER_PAGE = 10;

    /**
     * @param Request $request
     * @return QueryBuilderInterface
     */
    public function build(Request $request) : QueryBuilderInterface
    {
        $fields = [
            'subject',
            'text',
            'text',
            'title',
            'long_title',
            'text',
            'excerpt',
            'title',
            'description',
            'requirements',
            'recruitment'
        ];

        return (new QueryBuilder())
            ->must(new MultiMatch($request->input('q'), $fields))
            ->must(new ForumMustExist())
            ->should(new OnlyThoseWithAccess($request->attributes->get('forum_id')))
            ->sort(new Sort($request->get('sort', '_score'), $request->get('order', 'desc')))
            ->highlight(
                new Highlight(['subject', 'text', 'title', 'long_title', 'excerpt', 'description', 'requirements'])
            )
            ->size(($request->input('page', 1) - 1) * self::PER_PAGE, self::PER_PAGE);
    }
}
