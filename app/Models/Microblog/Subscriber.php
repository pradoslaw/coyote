<?php

namespace Coyote\Microblog;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use ForUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['microblog_id', 'user_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'microblog_subscribers';

    /**
     * @var array
     */
    public $timestamps = false;
}
