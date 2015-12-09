<?php

namespace Coyote\Forum;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['forum_id', 'user_id', 'section', 'hidden', 'order'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum_order';

    /**
     * @var array
     */
    public $timestamps = false;
}
