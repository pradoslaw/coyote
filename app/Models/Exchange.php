<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $currency_id
 * @property string $date
 * @property float $value
 */
class Exchange extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['currency_id', 'date', 'value'];
}
