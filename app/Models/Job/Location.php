<?php

namespace Coyote\Job;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['job_id', 'city'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Transform string (with cities separated by comma) to array
     *
     * @param string $cities Wroclaw, Warszawa
     * @return array
     * @todo Moze jest lepsze miejsce na umiesczenie tego kodu?
     */
    public static function transformToArray($cities)
    {
        return array_filter(array_unique(array_map('trim', preg_split('/[\/,]/', $cities))));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job()
    {
        return $this->belongsTo('Coyote\Job');
    }
}
