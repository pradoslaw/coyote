<?php

namespace Coyote\Services\Elasticsearch\Builders;

use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryString;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\Highlight;
use Illuminate\Http\Request;

class MixedBuilder extends QueryBuilder
{
    const PER_PAGE = 10;

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
        $fields = [
            'subject^2',
            'html',
            'title^2',
            'long_title',
            'posts.text',
            'excerpt',
            'title',
            'description',
            'requirements',
            'recruitment'
        ];

        $this
            ->must(new QueryString(preg_quote($this->request->input('q'), '/:'), $fields))
            ->must(new OnlyThoseWithAccess($this->request->attributes->get('forum_id')))
            ->sort(new Sort($this->request->get('sort', '_score'), $this->request->get('order', 'desc')))
            ->highlight(
                new Highlight(['subject', 'html', 'title', 'long_title', 'excerpt', 'description', 'requirements'])
            )
            ->size(($this->request->input('page', 1) - 1) * self::PER_PAGE, self::PER_PAGE);

        return parent::build();
    }
}
