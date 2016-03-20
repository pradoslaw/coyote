<?php

namespace Coyote\Job;

use Illuminate\Database\Eloquent\Model;

class Referer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_referers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['job_id', 'url', 'count'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $attributes = [
        'count' => 0
    ];
}
