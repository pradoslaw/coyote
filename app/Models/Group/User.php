<?php

namespace Coyote\Group;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['group_id', 'user_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'group_users';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'group_id';
}
