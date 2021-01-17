<?php

namespace Coyote;

use Carbon\Carbon;
use Coyote\Models\Scopes\ExcludeBlocked;
use Coyote\Notifications\ResetPasswordNotification;
use Coyote\Services\Media\Photo;
use Coyote\Services\Media\Factory as MediaFactory;
use Coyote\User\Relation;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\RoutesNotifications;
use Laravel\Passport\HasApiTokens;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property string $guest_id
 * @property bool $is_confirm
 * @property bool $is_blocked
 * @property int $group_id
 * @property string $group_name
 * @property int $visits
 * @property int $notifications
 * @property int $pm
 * @property int $notifications_unread
 * @property int $pm_unread
 * @property int $posts
 * @property int $allow_count
 * @property int $allow_subscribe
 * @property int $allow_smilies
 * @property int $allow_sig
 * @property int $allow_sticky_header
 * @property int $birthyear
 * @property int $reputation
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $provider
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $visited_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $date_format
 * @property string $timezone
 * @property string $ip
 * @property string $browser
 * @property string $website
 * @property string $github
 * @property string $location
 * @property float $latitude
 * @property float $longitude
 * @property string $firm
 * @property string $position
 * @property string $access_ip
 * @property string $sig
 * @property \Coyote\Services\Media\MediaInterface $photo
 * @property bool $is_online
 * @property bool $alert_login
 * @property \Coyote\Notification\Setting $notificationSettings[]
 * @property Group[]|\Illuminate\Support\Collection $groups
 * @property Group $group
 * @property Relation $relations
 * @property bool $is_sponsor
 * @property User[] $followers
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, RoutesNotifications, HasApiTokens, SoftDeletes, ExcludeBlocked;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * @var array
     */
    protected $attributes = ['date_format' => '%Y-%m-%d %H:%M'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_id',
        'photo',
        'date_format',
        'location',
        'latitude',
        'longitude',
        'website',
        'bio',
        'sig',
        'firm',
        'position',
        'birthyear',
        'allow_count',
        'allow_smilies',
        'allow_sig',
        'allow_subscribe',
        'allow_sticky_header'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'email', 'provider_id', 'provider', 'guest_id'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'visited_at', 'deleted_at'];

    /**
     * @var array
     */
    protected $casts = [
        'allow_smilies' => 'int',
        'allow_sig' => 'int',
        'allow_count' => 'int',
        'allow_subscribe' => 'bool',
        'allow_sticky_header' => 'int',
        'is_confirm' => 'int',
        'is_blocked' => 'bool',
        'is_online' => 'bool'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (User $model) {
            if (empty($model->guest_id)) {
                $model->guest_id = (string) Uuid::uuid4();
            }
        });

        static::saving(function (User $user) {
            // save group name. it rarely changes
            $user->group_name = $user->group_id ? $user->group->name : null;
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

        for ($i = 1950, $year = date('Y'); $i <= $year; $i++) {
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actkey()
    {
        return $this->hasMany('Coyote\Actkey');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function skills()
    {
        return $this->belongsToMany('Coyote\Tag', 'user_skills')->withPivot(['rate', 'order'])->orderByPivot('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany('Coyote\Invoice');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relations()
    {
        return $this->hasMany(Relation::class);
    }

    public function followers()
    {
        return $this->hasManyThrough(User::class, Relation::class, 'related_user_id', 'id', 'id', 'user_id')->where('user_relations.is_blocked', false);
    }

    /**
     * @param string $objectId
     * @return Model|null|static
     */
    public function getUnreadNotification($objectId)
    {
        return $this->hasOne(Notification::class)->where('object_id', '=', $objectId)->whereNull('read_at')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notificationSettings()
    {
        return $this->hasMany(Notification\Setting::class);
    }

    /**
     * @param string $value
     * @return \Coyote\Services\Media\MediaInterface
     */
    public function getPhotoAttribute($value)
    {
        if (!($value instanceof Photo)) {
            $photo = app(MediaFactory::class)->make('photo', ['file_name' => $value]);
            $this->attributes['photo'] = $photo;
        }

        return $this->attributes['photo'];
    }

    public function setIsActiveAttribute($value)
    {
        $this->setAttribute('deleted_at', !$value ? Carbon::now() : null);
    }

    public function getIsActiveAttribute()
    {
        return $this->deleted_at === null;
    }

    /**
     * @return bool
     */
    public function canReceiveEmail(): bool
    {
        return $this->email && !$this->deleted_at && $this->is_confirm && !$this->is_blocked;
    }

    /**
     * Get user's permissions (including all user's groups)
     *
     * @return mixed
     */
    public function getPermissions()
    {
        return $this
            ->permissions()
            ->join('permissions AS p', 'p.id', '=', 'group_permissions.permission_id')
            ->orderBy('value')
            ->select(['name', 'value'])
            ->get()
            ->pluck('value', 'name');
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function hasAccessByIp($ip)
    {
        if (empty($this->access_ip)) {
            return true;
        }

        $access = false;
        $ipParts = explode('.', $this->access_ip);

        for ($i = 0, $count = count($ipParts); $i < $count; $i += 4) {
            $regexp = str_replace('*', '.*', str_replace('.', '\.', implode('.', array_slice($ipParts, $i, 4))));

            if (preg_match('#^' . $regexp . '$#', $ip)) {
                $access = true;
                break;
            }
        }

        return $access;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'user:' . $this->id;
    }
}
