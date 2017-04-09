<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $code
 * @property bool $eu
 */
class Country extends Model
{
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
    public static function getCountriesList()
    {
        return self::pluck('name', 'id')->toArray();
    }
}
