<?php

namespace Coyote\Alert;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_types';

    /**
     * @var bool
     */
    public $timestamps = false;
}
