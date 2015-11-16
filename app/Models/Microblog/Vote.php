<?php

namespace Coyote\Microblog;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['microblog_id', 'user_id', 'ip'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'microblog_votes';

    /**
     * @var array
     */
    public $timestamps = false;
}
