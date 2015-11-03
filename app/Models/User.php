<?php namespace Coyote;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'salt', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'salt'];

    public static $rules = [
        'email'                         => 'email',
        'website'                       => 'url',
        'location'                      => 'string|size:50',
        'birthyear'                     => 'integer',
        'about'                         => 'string|size:255',
        'sig'                           => 'string|size:255',
        'allow_count'                   => 'boolean',
        'allow_smilies'                 => 'boolean',
        'allow_notify'                  => 'boolean',
        'allow_sig'                     => 'boolean',
    ];

    public static function birthYearList()
    {
        $result = [0 => '--'];

        for ($i = 1950; $i <= date('Y'); $i++) {
            $result[$i] = $i;
        }

        return $result;
    }

    public static function dateFormatList()
    {
        $dateFormats = [
            '%d-%m-%Y %H:%M',
            '%Y-%m-%d %H:%M',
            '%m/%d/%y %H:%M',
            '%d-%m-%y %H:%M',
            '%d %b %y %H:%M',
            '%d %B %Y, %H:%M'
        ];

        return array_combine($dateFormats, array_map(function ($value) {
            return strftime($value);
        }, $dateFormats));
    }
}
