<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'firewall';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['expire_at', 'user_id', 'ip', 'email', 'reason', 'moderator_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'expire_at'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';
}
