<?php

namespace Coyote\Job;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
