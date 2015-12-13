<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subject', 'anonymous_name', 'user_id', 'forum_id'];
}
