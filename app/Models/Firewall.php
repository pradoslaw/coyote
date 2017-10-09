<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $moderator_id
 * @property string $expires_at
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 * @property string $ip
 * @property string $fingerprint
 */
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
    protected $fillable = ['expire_at', 'user_id', 'ip', 'reason', 'moderator_id', 'fingerprint'];

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
