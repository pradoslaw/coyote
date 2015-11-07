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
    protected $fillable = ['name', 'email', 'salt', 'password', 'group_id', 'date_format', 'location', 'website', 'bio', 'sig', 'birthyear', 'allow_count', 'allow_smilies', 'allow_sig', 'allow_notify'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'salt'];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // jezeli nie wypelniono tych kolumn - ustawiamy na null
            foreach (['group_id', 'birthyear', 'website', 'location', 'sig', 'bio'] as $column) {
                if (empty($model->$column)) {
                    $model->$column = null;
                }
            }
        });
    }

    /**
     * Generuje liste z rocznikiem urodzenia (do wyboru m.in. w panelu uzytkownika)
     *
     * @return array
     */
    public static function birthYearList()
    {
        $result = [null => '--'];

        for ($i = 1950; $i <= date('Y'); $i++) {
            $result[$i] = $i;
        }

        return $result;
    }

    /**
     * Generuje liste mozliwych formatow daty do ustawienia w panelu uzytkownika
     *
     * @return array
     */
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

    /**
     * Sprawdza uprawnienie danego usera (w bazie danych) do wykonania danej czynnosci. Sprawdzane
     * sa wszystkie grupy uzytkownika do ktorych jest przypisany
     *
     * @param $ability
     * @return bool
     */
    public function check($ability)
    {
        if (is_null($this->permissions)) {
            $acl = Acl\Data::select(['name', 'value'])
                    ->join('user_groups AS ug', 'ug.user_id', '=', \DB::raw($this->id))
                    ->join('acl_permissions AS p', 'p.id', '=', 'acl_data.permission_id')
                    ->where('acl_data.group_id', '=', \DB::raw('ug.group_id'))
                    ->orderBy('value')
                    ->get()
                    ->lists('value', 'name');

            $this->permissions = json_encode($acl);
            $this->save();
        } else {
            $acl = json_decode($this->permissions, true);
        }

        return isset($acl[$ability]) ? $acl[$ability] : false;
    }

    /**
     * Zapis ustawienia dla danego uzytkownika (np. ukrycie danych elementow na stronie czy zamkniecie slidera)
     * Typ kolumny settings w bazie danych to JSON wiec mozna zapisywac wiele roznych opcji dla danego usera
     *
     * @param $name
     * @param $value
     */
    public function setSetting($name, $value)
    {
        if (is_null($this->settings)) {
            $settings = [];
        } else {
            $settings = json_decode($this->settings, true);
        }

        $settings[$name] = $value;
        $this->settings = json_encode($settings);
        $this->save();
    }

    /**
     * Zwraca ustawienie zapisane dla danego usera. Jezeli nie jest ustawione w rekordzie, zwraca domyslna
     * wartosc okreslona w parametrze $default
     *
     * @param $name
     * @param null $default
     * @return null|mixed
     */
    public function getSetting($name, $default = null)
    {
        if (is_null($this->settings)) {
            return $default;
        } else {
            $settings = json_decode($this->settings, true);

            return isset($settings[$name]) ? $settings[$name] : null;
        }
    }
}
