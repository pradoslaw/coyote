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

    public function scopeForAll($query)
    {
        return $query->orWhereNotExists(function ($sub) {
            return $sub->select('forum_id')
                    ->from('forum_access')
                    ->where('forum_access.forum_id', '=', \DB::raw('forums.id'));
        });
    }

    public function scopeForGroups($query, $groupsId)
    {
        return $query->whereExists(function ($sub) use ($groupsId) {
            return $sub->select('forum_id')
                    ->from('forum_access')
                    ->whereIn('group_id', $groupsId)
                    ->where('forum_access.forum_id', '=', \DB::raw('forums.id'));
        });
    }

    public function access()
    {
        return $this->hasMany('Coyote\Forum\Access');
    }
}
