<?php

namespace Coyote\User;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
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
    protected $table = 'user_groups';

    public $timestamps = false;
    protected $primaryKey = 'group_id';
}
