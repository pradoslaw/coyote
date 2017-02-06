<?php

namespace Coyote\Job;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['job_id', 'feature_id', 'value', 'is_checked'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_features';

    /**
     * @var array
     */
    public $timestamps = false;
}
