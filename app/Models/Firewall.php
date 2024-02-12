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
 * @property \Coyote\User $user
 */
class Firewall extends Model
{
    protected $table = 'firewall';

    protected $fillable = ['expire_at', 'user_id', 'ip', 'reason', 'moderator_id', 'fingerprint'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expire_at'  => 'datetime',
    ];

    protected $dateFormat = 'Y-m-d H:i:se';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
