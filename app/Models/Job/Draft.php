<?php

namespace Coyote\Models\Job;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'key', 'value'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_drafts';

    /**
     * @var array
     */
    public $timestamps = false;
}
