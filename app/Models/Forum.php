<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'name', 'path', 'description', 'section', 'url'];

    /**
     * @var bool
     */
    public $timestamps = false;

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
        $usersId = $this->access()
                ->select('user_id')
                ->join('group_users', 'group_users.group_id', '=', 'forum_access.group_id')
                ->lists('user_id')
                ->toArray();

        if (empty($usersId)) {
            return true;
        } elseif (!$userId && count($usersId)) {
            return false;
        } else {
            return in_array($userId, $usersId);
        }
    }
}
