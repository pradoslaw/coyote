<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use Searchable;

    const UPDATED_AT = null;

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
        'login',
        'email'
    ];
}
