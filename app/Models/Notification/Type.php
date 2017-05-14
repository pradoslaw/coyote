<?php

namespace Coyote\Notification;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notification_types';

    /**
     * @var bool
     */
    public $timestamps = false;
}
