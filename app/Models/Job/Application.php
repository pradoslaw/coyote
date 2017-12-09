<?php

namespace Coyote\Job;

use Coyote\Models\Scopes\ForGuest;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $job_id
 * @property string $guest_id
 * @property string $email
 * @property string $name
 * @property string $phone
 * @property string $github
 * @property string $text
 * @property string $cv
 * @property string $salary
 * @property string $dismissal_period
 */
class Application extends Model
{
    use ForGuest;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['job_id', 'guest_id', 'email', 'name', 'phone', 'github', 'text', 'salary', 'dismissal_period', 'cv'];

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
}
