<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Actkey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['actkey', 'email', 'user_id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'actkey';

    /**
     * @param int $userId
     * @param string $email
     * @return string
     */
    public static function createLink($userId, $email = null)
    {
        self::create([
            'actkey'   => $code = str_random(),
            'user_id'  => $userId,
            'email' => $email
        ]);

        // taki format linku zachowany jest ze wzgledu na wsteczna kompatybilnosc.
        // z czasem mozemy zmienic ten format aby wskazywal na /User/Confirm/Email/<id>/<actkey>
        // @todo...
        return url('Confirm/Email') . '?id=' . $userId . '&actkey=' . $code;
    }
}
