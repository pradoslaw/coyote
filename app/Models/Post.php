<?php
namespace Coyote;

use Carbon\Carbon;
use Coyote;
use Coyote\Models\Asset;
use Coyote\Models\Subscription;
use Coyote\Post\Accept;
use Coyote\Post\Log;
use Coyote\Post\Vote;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $forum_id
 * @property int $topic_id
 * @property int $score
 * @property int $edit_count
 * @property int $editor_id
 * @property int $deleter_id
 * @property string $delete_reason
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property string $user_name
 * @property string $text
 * @property string $html
 * @property string $ip
 * @property string $browser
 * @property Forum $forum
 * @property Topic $topic
 * @property Asset[] $assets
 * @property Vote[]|Eloquent\Collection $votes
 * @property Coyote\Post\Comment[]|Eloquent\Collection $comments
 * @property Flag[] $flags
 * @property User|null $user
 * @property User|null $editor
 * @property User|null $deleter
 * @property Accept|null $accept
 * @property int|null tree_parent_post_id
 * @property Post|null $treeParentPost
 */
class Post extends Model
{
    use SoftDeletes;

    protected $attributes = ['score' => 0];
    protected $fillable = ['topic_id', 'forum_id', 'user_id', 'user_name', 'text', 'ip', 'browser', 'edit_count', 'editor_id'];
    protected $dateFormat = 'Y-m-d H:i:se';
    protected $casts = [
        'deleted_at' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    private string|null $html = null;

    public static function boot(): void
    {
        parent::boot();
        static::restoring(function (Post $post) {
            $post->deleter_id = null;
            $post->delete_reason = null;
        });
        static::saved(function (Post $post) {
            if ($post->isDirtyWithRelations()) {
                $topic = $post->topic()->withTrashed()->first();
                $post->logs()->create(
                    array_merge(
                        $post->only(['user_id', 'text', 'ip', 'browser']),
                        ['title' => $topic->title, 'user_id' => $post->editor_id ?: $post->user_id],
                    ),
                );
            }
        });
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Coyote\Post\Comment::class)->orderBy('id');
    }

    public function subscribers(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'resource');
    }

    public function flags(): MorphToMany
    {
        return $this->morphToMany(Flag::class, 'resource', 'flag_resources');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(Asset::class, 'content');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function accept(): HasOne
    {
        return $this->hasOne(Accept::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class)->withTrashed();
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function treeParentPost(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Enable/disable subscription for this post
     *
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($userId, $flag)
    {
        if ($flag) {
            $this->subscribers()->firstOrCreate(['user_id' => $userId]);
        } else {
            $this->subscribers()->forUser($userId)->delete();
        }
    }

    public function getHtmlAttribute(): string
    {
        if ($this->html === null) {
            $this->html = app('parser.post')->parse($this->text);
        }
        return $this->html;
    }

    public function previous(): ?Post
    {
        /** @var Post|null $previous */
        $previous = static::query()
            ->where('topic_id', $this->topic_id)
            ->where('created_at', '<', $this->created_at)
            ->orderBy('created_at', 'DESC')
            ->first();
        return $previous;
    }

    public function deleteWithReason(int $userId, ?string $reason): void
    {
        $this->deleter_id = $userId;
        $this->delete_reason = $reason;
        $this->{$this->getDeletedAtColumn()} = $this->freshTimestamp();
        $this->save();
    }

    public function isDirtyWithRelations(): bool
    {
        return $this->isDirty() || $this->topic->isDirty();
    }
}
