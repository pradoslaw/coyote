<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property string $payload
 * @property string $url
 * @property string $browser
 * @property string $robot
 * @property string $ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Session extends Model
{
    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';
}
