<?php

namespace Coyote\Topic;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use ForUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'user_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'topic_users';

    /**
     * @var array
     */
    public $timestamps = false;
}
