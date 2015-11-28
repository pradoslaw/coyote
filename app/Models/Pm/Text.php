<?php

namespace Coyote\Pm;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    /**
     * @var string
     */
    protected $table = 'pm_text';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['text'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
