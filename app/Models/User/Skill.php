<?php

namespace Coyote\User;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'rate', 'order'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_skills';

    /**
     * @var bool
     */
    public $timestamps = false;
}
