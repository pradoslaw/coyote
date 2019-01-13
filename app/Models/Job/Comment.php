<?php

namespace Coyote\Job;

use Coyote\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User $user
 * @property int $user_id
 * @property int $job_id
 * @property int $parent_id
 * @property string $email
 * @property string $text
 */
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
