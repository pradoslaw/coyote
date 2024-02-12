<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property int $user_id
 * @property string $subject
 * @property \Carbon\Carbon $created_at
 */
class Mail extends Model
{
    protected $fillable = ['subject', 'email'];

    protected $casts = ['created_at' => 'datetime'];
}
