<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property array predictions
 */
class Guest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'session_id', 'predictions'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var bool
     */
    public $incrementing = false;
}
