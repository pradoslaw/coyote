<?php

namespace Coyote\Firm;

use Illuminate\Database\Eloquent\Model;

class Benefit extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'firm_benefits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['firm_id', 'name'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array
     */
    public static function getBenefitsList()
    {
        return [
            'Karta multisport (lub podobna)',
            'Kuchnia',
            'Darmowe przekąski',
            'Darmowe posiłki',
            'Darmowa kawa i inne napoje',
            'Darmowy parking',
            'Prywatna opieka zdrowotna',
            'Pokój gier',
            'Elastyczne godziny pracy',
            'Siłownia',
            'Szkolenia',
            'Konferencje',
            'Prysznic',
            'Telefon służbowy'
        ];
    }
}
