<?php

namespace Coyote\Forum;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum_reasons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
