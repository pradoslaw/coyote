<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * Nie bedziemy dodawac nowych uprawnien z poziomu Laravel
     *
     * @var array
     */
    protected $guarded = ['name', 'description', 'default'];
}
