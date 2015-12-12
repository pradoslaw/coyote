<?php

namespace Coyote\Group;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['group_id', 'user_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'group_users';

    public $timestamps = false;
    protected $primaryKey = 'group_id';

    /**
     * Pobranie listy grup do ktorych dostep ma user (ID, nazwa)
     *
     * @param $userId
     * @return mixed
     */
    public static function groupList($userId)
    {
        return self::select(['groups.id', 'name'])
                    ->where('user_id', '=', $userId)
                    ->join('groups', 'groups.id', '=', \DB::raw('group_id'))
                    ->lists('name', 'id');
    }
}
