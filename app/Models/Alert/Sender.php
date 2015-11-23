<?php

namespace Coyote\Alert;

use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_senders';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['alert_id', 'user_id', 'name'];
}
