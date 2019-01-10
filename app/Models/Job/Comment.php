<?php

namespace Coyote\Models\Job;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'job_id', 'email', 'parent_id', 'text'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_comments';

}
