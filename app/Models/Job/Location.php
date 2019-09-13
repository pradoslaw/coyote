<?php

namespace Coyote\Job;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $job_id
 * @property string $city
 * @property double $latitude
 * @property double $longitude
 */
class Location extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['city', 'latitude', 'longitude', 'address', 'country'];

    /**
     * @var array
     */
    protected $attributes = ['city' => '', 'latitude' => null, 'longitude' => null];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job()
    {
        return $this->belongsTo('Coyote\Job');
    }

    /**
     * @param $city
     */
    public function setCityAttribute($city)
    {
        $this->attributes['city'] = capitalize($city);
    }
}
