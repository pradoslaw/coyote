<?php

namespace Coyote\Services\Elasticsearch\Builders;

use Coyote\Services\Elasticsearch\Filters\Post\OnlyThoseWithAccess;
use Coyote\Services\Elasticsearch\Filters\Terms;
use Coyote\Services\Elasticsearch\QueryBuilder;
use Coyote\Services\Elasticsearch\QueryString;
use Coyote\Services\Elasticsearch\Sort;
use Coyote\Services\Elasticsearch\Highlight;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MixedBuilder extends QueryBuilder
{
    const PER_PAGE = 10;

    const TOPIC = 'topic';
    const MICROBLOG = 'microblog';
    const WIKI = 'wiki';
    const JOB = 'job';

    private const DEFAULT = [self::TOPIC, self::MICROBLOG, self::WIKI, self::JOB];

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
            'text',
            'title^2',
            'long_title',
            'posts.text',
            'excerpt',
            'title',
            'description',
            'recruitment'
        ];

        $models = self::DEFAULT;

        if ($this->request->filled('type')) {
            $validator = validator($this->request->toArray(), ['type' => 'nullable', Rule::in(self::DEFAULT)]);

            if (!$validator->fails()) {
                $models = $this->request->input('type');
            }
        }

        $this
            ->must(new Terms('model', $models))
            ->must(new QueryString(preg_quote($this->request->input('q'), '/:'), $fields))
            ->must(new OnlyThoseWithAccess($this->request->attributes->get('forum_id')))
            ->sort(new Sort($this->request->get('sort', '_score'), $this->request->get('order', 'desc')))
            ->highlight(
                new Highlight(['subject', 'text', 'title', 'long_title', 'excerpt', 'description', 'requirements'])
            )
            ->size(($this->request->input('page', 1) - 1) * self::PER_PAGE, self::PER_PAGE);

        return parent::build();
    }
}
