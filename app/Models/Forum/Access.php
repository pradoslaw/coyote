<?php

namespace Coyote\Forum;

use Coyote\Group;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['forum_id', 'group_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum_access';

    /**
     * @var array
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'group_id';

    /**
     * @var bool
     */
    public $incrementing = false;
}
