<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
    protected $dates = ['created_at', 'deleted_at'];
    public $timestamps = false;
}
