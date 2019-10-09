<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $int
 * @property string $code
 * @property int $amount
 * @property int $user_id
 */
class Coupon extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'amount', 'user_id'];

    /**
     * @var array
     */
    protected $attributes = ['code' => null, 'amount' => 0];

    /**
     * @var bool
     */
    public $timestamps = false;
}
