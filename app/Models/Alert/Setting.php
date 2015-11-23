<?php

namespace Coyote\Alert;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_settings';

    /**
     * @var bool
     */
    public $timestamps = false;
}
