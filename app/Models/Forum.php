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
     * @param $query
     * @return mixed
     */
    public function scopeForAll($query)
    {
        return $query->orWhereNotExists(function ($sub) {
            return $sub->select('forum_id')
                    ->from('forum_access')
                    ->where('forum_access.forum_id', '=', \DB::raw('forums.id'));
        });
    }

    /**
     * @param $query
     * @param $groupsId
     * @return mixed
     */
    public function scopeForGroups($query, $groupsId)
    {
        return $query->whereExists(function ($sub) use ($groupsId) {
            return $sub->select('forum_id')
                    ->from('forum_access')
                    ->whereIn('group_id', $groupsId)
                    ->where('forum_access.forum_id', '=', \DB::raw('forums.id'));
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
     * Determines if user can access to forum
     *
     * @param int $userId
     * @return bool
     */
    public function userCanAccess($userId)
    {
        $usersId = $this->access()
                ->select('user_id')
                ->join('user_groups', 'user_groups.group_id', '=', 'forum_access.group_id')
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
