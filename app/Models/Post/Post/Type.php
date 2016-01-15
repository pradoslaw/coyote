<?php

namespace Coyote\Post\History;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_history_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public $increments = false;
}
