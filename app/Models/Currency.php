<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    const PLN = 1;
    const EUR = 2;
    const USD = 3;
    const GBP = 4;
    const CHF = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array
     */
    public static function getCurrenciesList()
    {
        return self::pluck('name', 'id')->toArray();
    }
}
