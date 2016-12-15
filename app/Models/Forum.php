<?php

namespace Coyote;

use Coyote\Models\Scopes\TrackForum;
use Coyote\Models\Scopes\TrackTopic;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $topics
 * @property int $posts
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
        return $this->hasMany('Coyote\Forum\Access');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function permissions()
    {
        return $this->hasMany('Coyote\Forum\Permission');
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
                        ->lists('value', 'name');
        }

        return isset($acl[$name]) ? $acl[$name] : false;
    }

    /**
     * Determines if user can access to forum
     *
     * @param int $userId
     * @return bool
     */
    public function userCanAccess($userId)
    {
        $usersId = $this->getUsersWithAccess();

        if (empty($usersId)) {
            return true;
        } elseif (!$userId && count($usersId)) {
            return false;
        } else {
            return in_array($userId, $usersId);
        }
    }

    /**
     * Filter users. Return only ids of users who have access to this forum.
     *
     * @param array $usersId
     * @return array|bool
     */
    public function onlyUsersWithAccess(array $usersId)
    {
        if (empty($usersId)) {
            return false;
        }

        $allowed = $this->getUsersWithAccess();
        if (empty($allowed)) {
            return $usersId;
        }

        return array_intersect($usersId, $allowed);
    }

    /**
     * @param $userId
     * @param $sessionId
     * @return mixed
     */
    public function markTime($userId, $sessionId)
    {
        $sql = $this->tracks()->select('marked_at');

        if ($userId) {
            $sql->where('user_id', $userId);
        } else {
            $sql->where('session_id', $sessionId);
        }

        return $sql->value('marked_at');
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
                            ->lists('user_id')
                            ->toArray();
        }

        return $usersId;
    }
}
