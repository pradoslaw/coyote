<?php

namespace Coyote\Pm;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    const UPDATED_AT = null;

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
     * @var array
     */
    public $casts = ['created_at' => 'datetime'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';
}
