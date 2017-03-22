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
    /**
     * @var array
     */
    protected $fillable = ['subject', 'email'];

    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this;
    }
}
