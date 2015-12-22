<?php

namespace Coyote\Topic;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'forum_id', 'user_id', 'marked_at', 'session_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'topic_track';

    /**
     * @var array
     */
    public $timestamps = false;
}
