<?php

namespace Coyote\Post;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Accept extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'user_id', 'topic_id', 'ip'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_accepts';

    /**
     * @var array
     */
    public $timestamps = false;
}
