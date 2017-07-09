<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $int
 * @property string $code
 * @property int $amount
 */
class Coupon extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'amount'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
