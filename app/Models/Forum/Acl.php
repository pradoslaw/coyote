<?php

namespace Coyote\Forum;

use Illuminate\Database\Eloquent\Model;

class Acl extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['forum_id', 'group_id', 'permission_id', 'value'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum_acl';

    /**
     * @var array
     */
    public $timestamps = false;
}
