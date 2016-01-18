<?php

namespace Coyote\Poll;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * @var string
     */
    protected $table = 'poll_votes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'poll_id', 'ip'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
