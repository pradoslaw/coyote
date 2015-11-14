<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Microblog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'user_id', 'text'];
}
