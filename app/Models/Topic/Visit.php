<?php

namespace Coyote\Topic;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'user_id', 'visits'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'topic_visits';
}
