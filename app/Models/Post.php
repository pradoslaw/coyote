<?php

namespace Coyote;

use Coyote\Post\Subscriber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $forum_id
 * @property int $topic_id
 * @property int $score
 * @property int $edit_count
 * @property int $editor_id
 * @property int $deleter_id
 * @property string $delete_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $updated_at
 * @property string $user_name
 * @property string $text
 * @property string $html
 * @property string $ip
 * @property string $browser
 * @property string $host
 * @property \Coyote\Forum $forum
 * @property \Coyote\Topic $topic
 * @property \Coyote\Post\Attachment[] $attachments
 * @property \Coyote\Post\Vote[] $votes
 * @property \Coyote\Post\Comment[] $comments
 * @property \Coyote\User $user
 * @property \Coyote\User $deleter
 * @property \Coyote\Post\Accept $accept
 */
class Post extends Model
{
    use SoftDeletes;
    use Searchable;

    protected $attributes = ['score' => 0];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'forum_id', 'user_id', 'user_name', 'text', 'ip', 'browser', 'host', 'edit_count', 'editor_id'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Related to Laravel 5.8. deleted_at has different date format that created_at and carbon throws exception
     *
     * @var string[]
     */
    protected $casts = ['deleted_at' => 'string'];

    /**
     * Html version of the post.
     *
     * @var null|string
     */
    private $html = null;

    public static function boot()
    {
        parent::boot();

        static::restoring(function (Post $post) {
            $post->deleter_id = null;
            $post->delete_reason = null;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Coyote\Post\Comment')->orderBy('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers()
    {
        return $this->hasMany(Subscriber::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Coyote\Post\Attachment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany('Coyote\Post\Vote');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accept()
    {
        return $this->hasOne('Coyote\Post\Accept');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo('Coyote\Topic');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forum()
    {
        return $this->belongsTo('Coyote\Forum');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function editor()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return mixed
     */
    public function deleter()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('Coyote\Post\Log');
    }

    /**
     * Enable/disable subscription for this post
     *
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($userId, $flag)
    {
        if (!$flag) {
            $this->subscribers()->where('user_id', $userId)->delete();
        } else {
            $this->subscribers()->firstOrCreate(['post_id' => $this->id, 'user_id' => $userId]);
        }
    }

    /**
     * @return string
     */
    public function getHtmlAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.post')->parse($this->text);
    }

    /**
     * @param array $ids
     */
    public function syncAttachments($ids)
    {
        foreach ($this->attachments as $attachment) {
            $attachment->post()->dissociate()->save();
        }

        foreach ($ids as $id) {
            Post\Attachment::find($id)->post()->associate($this)->save();
        }
    }

    /**
     * Get previous post. We use it in merge controller.
     *
     * @return mixed
     */
    public function previous()
    {
        return (new static)
            ->where('topic_id', $this->topic_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * @param int $userId
     * @param string|null $reason
     */
    public function deleteWithReason(int $userId, ?string $reason)
    {
        $this->deleter_id = $userId;
        $this->delete_reason = $reason;
        $this->{$this->getDeletedAtColumn()} = $this->freshTimestamp();

        $this->save();
    }
}
