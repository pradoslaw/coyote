<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $partner
 */
class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'user_id', 'partner'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public function users()
    {
        // group belongs to many users
    }

    /**
     * @return $this
     */
    public function permissions()
    {
        return $this->belongsToMany('Coyote\Permission', 'group_permissions')->withPivot('value');
    }
}
