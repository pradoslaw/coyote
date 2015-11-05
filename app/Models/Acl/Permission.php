<?php

namespace Coyote\Acl;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'acl_permission';
    /**
     * Nie bedziemy dodawac nowych uprawnien z poziomu Laravel
     *
     * @var array
     */
    protected $guarded = ['name', 'description', 'default'];
}
