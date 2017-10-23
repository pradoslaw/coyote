<?php

namespace Coyote\Forum;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['forum_id', 'guest_id', 'marked_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'forum_track';

    /**
     * @var array
     */
    public $timestamps = false;
}
