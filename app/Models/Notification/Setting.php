<?php

namespace Coyote\Notification;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notification_settings';

    /**
     * @var bool
     */
    public $timestamps = false;
}
