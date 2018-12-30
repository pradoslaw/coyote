<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['forum_id', 'user_id', 'content_type', 'content_id', 'excerpt'];
}
