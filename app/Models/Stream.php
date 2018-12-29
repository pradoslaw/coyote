<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $casts = ['actor' => 'array', 'object' => 'array', 'target' => 'array'];
}
