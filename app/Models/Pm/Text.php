<?php

namespace Coyote\Pm;

use Coyote\WithoutUpdatedAt;
use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    use WithoutUpdatedAt;

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
    public $dates = ['created_at'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';
}
