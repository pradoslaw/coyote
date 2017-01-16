<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 2017-01-16
 * Time: 19:29
 */

namespace Coyote\Services\Elasticsearch\Filters\Post;

use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class ForumMustExist implements DslInterface
{
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return [
            'bool' => [
                'must_not' => [
                    'exists' => [
                        'field' => 'forum_id'
                    ]
                ]
            ]
        ];
    }
}
