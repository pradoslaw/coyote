<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use SoftDeletes, Sortable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subject', 'path', 'forum_id', 'is_sticky', 'is_announcement'];

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
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereExists(function ($sub) use ($userId) {
            return $sub->select('topic_id')
                ->from('topic_users')
                ->where('user_id', $userId);
        });
    }

    /**
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeSubscribes($query, $userId)
    {
        return $query->whereExists(function ($sub) use ($userId) {
            return $sub->select('topic_id')
                ->from('topic_subscribers')
                ->where('user_id', $userId);
        });
    }

    public function tags()
    {
        return $this->hasMany('Coyote\Topic\Tag')->join('tags', 'tags.id', '=', 'tag_id');
    }

    public function subscribers()
    {
        return $this->hasMany('Coyote\Topic\Subscriber');
    }
}
