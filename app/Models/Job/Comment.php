<?php

namespace Coyote\Job;

use Coyote\Job;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property User $user
 * @property int $user_id
 * @property int $job_id
 * @property int $parent_id
 * @property string $email
 * @property string $text
 * @property Comment[]|\Illuminate\Support\Collection $children
 * @property Job $job
 * @property Comment $parent
 * @property string $html
 */
class Comment extends Model
{
    use Notifiable;

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
     * @var array
     */
    protected $casts = ['parent_id' => 'int', 'job_id' => 'int'];

    /**
     * @var array
     */
    protected $appends = ['html'];

    /**
     * @var null|string
     */
    private $html = null;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent()
    {
        return $this->hasOne(Comment::class, 'id', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @return string
     */
    public function getHtmlAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.job.comment')->parse($this->text);
    }

    /**
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email ?: $this->user->email;
    }
}
