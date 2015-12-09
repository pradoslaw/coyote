<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'name', 'path', 'description', 'section', 'url'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
