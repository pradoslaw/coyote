<?php

namespace Coyote\Job;

use Coyote\Models\Scopes\ForUser;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User $user
 */
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
