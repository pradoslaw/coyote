<?php

namespace Coyote\Services\Elasticsearch\Filters\Post;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class OnlyThoseWithAccess implements DslInterface
{
    /**
     * @var int[]
     */
    private $forumId;

    /**
     * @param int|int[] $forumId
     */
    public function __construct($forumId)
    {
        if (!is_array($forumId)) {
            $forumId = [$forumId]; // make array
        }

        $this->forumId = $forumId;
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'terms' => [
                            'forum.id' => $this->forumId
                        ]
                    ],
                    [
                        'bool' => [
                            'must_not' => [
                                [
                                    'exists' => [
                                        'field' => 'forum.id'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
