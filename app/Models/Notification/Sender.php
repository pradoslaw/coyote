<?php

namespace Coyote\Notification;

use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notification_senders';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_id', 'user_id', 'name'];
}
