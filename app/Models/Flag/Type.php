<?php

namespace Coyote\Flag;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flag_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'description'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
