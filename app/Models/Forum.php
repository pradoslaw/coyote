<?php

namespace Coyote;

use Carbon\Carbon;
use Coyote\Forum\Access;
use Coyote\Forum\Track as Forum_Track;
use Coyote\Models\Scopes\TrackForum;
use Coyote\Models\Scopes\TrackTopic;
use Coyote\Topic\Track as Topic_Track;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $topics
 * @property int $posts
 * @property int $redirects
 * @property int $order
 * @property int $last_post_id
 * @property int $is_locked
 * @property int $require_tag
 * @property int $enable_prune
 * @property int $enable_reputation
 * @property int $enable_anonymous
 * @property int $prune_days
 * @property int $prune_last
 * @property string $name
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $section
 * @property string $url
 * @property Forum $parent
 * @property Forum\Track[] $tracks
 */
class Forum extends Model
{
    use TrackTopic, TrackForum;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'title',
        'slug',
        'description',
        'section',
        'url',
        'order',
        'is_locked',
        'require_tag',
        'enable_reputation',
        'enable_anonymous'
    ];

    /**
     * @var array
     */
    protected $casts = ['redirects' => 'int'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function (Forum $model) {
            if (empty($model->parent_id)) {
                $model->parent_id = null;
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function access()
    {
        return $this->hasMany(Access::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function groups()
    {
        return $this->hasManyThrough(Group::class, Access::class, 'forum_id', 'id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(\Coyote\Forum\Permission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracks()
    {
        return $this->hasMany('Coyote\Forum\Track');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne('Coyote\Page', 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order()
    {
        return $this->hasMany('Coyote\Forum\Order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne('Coyote\Forum', 'id', 'parent_id');
    }

    /**
     * @param string $name
     */
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = $name;
    }

    /**
     * Checks ability for specified forum and user id
     *
     * @param string $name
     * @param int $userId
     * @return bool
     */
    public function ability($name, $userId)
    {
        static $acl = null;

        if (is_null($acl)) {
            $acl = $this->permissions()
                        ->join('permissions AS p', 'p.id', '=', 'forum_permissions.permission_id')
                        ->join('group_users AS ug', 'ug.group_id', '=', 'forum_permissions.group_id')
                        ->where('ug.user_id', $userId)
                        ->orderBy('value')
                        ->select(['name', 'value'])
                        ->pluck('value', 'name');
        }

        return isset($acl[$name]) ? $acl[$name] : false;
    }

    /**
     * @param string $guestId
     * @return mixed
     */
    public function markTime($guestId)
    {
        return $this->tracks()->select('marked_at')->where('guest_id', $guestId)->value('marked_at');
    }

    /**
     * Mark forum as read
     *
     * @param $forumId
     * @param $guestId
     */
    public function markAsRead($guestId)
    {
        $markTime = Carbon::now();

        $sql = "INSERT INTO forum_track (forum_id, guest_id, marked_at) 
                VALUES(?, ?, ?)
                ON CONFLICT ON CONSTRAINT forum_track_forum_id_guest_id_unique DO 
                UPDATE SET marked_at = ?";

        $this->getConnection()->statement($sql, [$this->id, $guestId, $markTime, $markTime]);
    }

    /**
     * Scope a query to only given user id.
     *
     * @param Builder $builder
     * @param array $columns
     * @return Builder
     */
    public function scopeLateSelect(Builder $builder, $columns)
    {
        $builder->withGlobalScope('lateSelect', function (Builder $builder) use ($columns) {
            return $builder->addSelect($columns);
        });

        return $builder;
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return mixed
     */
    protected function getUsersWithAccess()
    {
        static $usersId = null;

        if (is_null($usersId)) {
            $usersId = $this->access()
                            ->select('user_id')
                            ->join('group_users', 'group_users.group_id', '=', 'forum_access.group_id')
                            ->pluck('user_id')
                            ->toArray();
        }

        return $usersId;
    }
}
