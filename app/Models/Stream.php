<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use WithoutUpdatedAt;

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $casts = ['actor' => 'array', 'object' => 'array', 'target' => 'array'];

    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
        'verb',
        'actor',
        'object',
        'target',
        'ip',
        'browser',
        'fingerprint',
        'login'
    ];

    /**
     * Elasticsearch type mapping
     *
     * @var array
     */
    protected $mapping = [
        "actor" => [
            "type" => "object",
            "properties" => [
                "name" => [
                    "type" => "string",
                    // ability to search case insensitive
                    "analyzer" => "keyword_analyzer"
                ]
            ]
        ],
        "ip" => [
            "type" => "string",
            "index" => "not_analyzed"
        ],
        "browser" => [
            "type" => "text",
            "index" => "not_analyzed"
        ],
        "fingerprint" => [
            "type" => "string",
            "index" => "not_analyzed"
        ],
        "created_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ]
    ];
}
