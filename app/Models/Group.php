<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'user_id'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public function users()
    {
        // group belongs to many users
    }

    public function permissions()
    {
        return $this->belongsToMany('Coyote\Permission', 'group_permissions');
    }
}
