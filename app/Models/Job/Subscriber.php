<?php

namespace Coyote\Job;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use ForUser;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_subscribers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['job_id', 'user_id'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
