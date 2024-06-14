<?php

namespace Coyote;

use Carbon\Carbon;
use Coyote;
use Coyote\Models\Flag\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $type_id
 * @property int $user_id
 * @property int $moderator_id
 * @property string $url
 * @property mixed $metadata
 * @property string $text
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Flag\Type $type
 * @property User $user
 * @property Forum[] $forums
 * @property Topic[] $topics
 * @property Post[] $posts
 * @property Job[] $jobs
 * @property Microblog[] $microblogs
 */
class Flag extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = ['type_id', 'user_id', 'url', 'text', 'moderator_id'];
    protected $dateFormat = 'Y-m-d H:i:se';
    protected $casts = ['deleted_at' => 'string', 'created_at' => 'datetime'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(Flag\Type::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'resource', 'flag_resources');
    }

    public function topics(): MorphToMany
    {
        return $this->morphedByMany(Topic::class, 'resource', 'flag_resources');
    }

    public function forums(): MorphToMany
    {
        return $this->morphedByMany(Forum::class, 'resource', 'flag_resources');
    }

    public function microblogs(): MorphToMany
    {
        return $this->morphedByMany(Microblog::class, 'resource', 'flag_resources');
    }

    public function jobs(): MorphToMany
    {
        return $this->morphedByMany(Job::class, 'resource', 'flag_resources');
    }

    public function comments(): MorphToMany
    {
        return $this->morphedByMany(Coyote\Comment::class, 'resource', 'flag_resources');
    }

    public function postComments(): MorphToMany
    {
        return $this->morphedByMany(Coyote\Post\Comment::class, 'resource', 'flag_resources');
    }
}
