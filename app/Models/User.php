<?php namespace Coyote;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Cache;

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
    protected $fillable = ['name', 'email', 'salt', 'password', 'group_id', 'is_active', 'is_confirm', 'provider', 'provider_id', 'photo', 'date_format', 'location', 'website', 'bio', 'sig', 'firm', 'position', 'birthyear', 'allow_count', 'allow_smilies', 'allow_sig', 'allow_subscribe'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'salt'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'visited_at'];

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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group()
    {
        return $this->hasOne('Coyote\Group', 'id', 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('Coyote\Group', 'group_users');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function permissions()
    {
        return $this->hasManyThrough('Coyote\Group\Permission', 'Coyote\Group\User', 'user_id', 'group_id');
    }

    /**
     * Cache permissions for this user
     *
     * @return mixed
     */
    private function getPermissions()
    {
        return $this->permissions()
                ->join('permissions AS p', 'p.id', '=', 'group_permissions.permission_id')
                ->orderBy('value')
                ->select(['name', 'value'])
                ->get()
                ->lists('value', 'name');
    }

    /**
     * Sprawdza uprawnienie danego usera (w bazie danych) do wykonania danej czynnosci. Sprawdzane
     * sa wszystkie grupy uzytkownika do ktorych jest przypisany
     *
     * @param $ability
     * @return bool
     */
    public function ability($ability)
    {
        // @todo nie powinnismy uzywac cache w modelu
        if (Cache::getFacadeRoot() instanceof \Illuminate\Cache\TaggableStore) {
            $key = 'permission:' . $this->id;

            $permissions = Cache::tags(['permissions', $key])->rememberForever($key, function () {
                return $this->getPermissions();
            });
        } else {
            $permissions = $this->getPermissions();
        }
        return isset($permissions[$ability]) ? $permissions[$ability] : false;
    }

    /**
     * @param $group
     */
    public function attachGroup($group)
    {
        if (is_object($group)) {
            $group = $group->getKey();
        }

        $this->groups()->attach($group);
        Cache::tags('permission:' . $this->id)->flush();
    }

    /**
     * @param $group
     */
    public function detachGroup($group)
    {
        if (is_object($group)) {
            $group = $group->getKey();
        }

        $this->groups()->detach($group);
        Cache::tags('permission:' . $this->id)->flush();
    }
}
