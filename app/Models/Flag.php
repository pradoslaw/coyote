<?php

namespace Coyote;

use Coyote\Models\Flag\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $type_id
 * @property int $user_id
 * @property int $moderator_id
 * @property string $url
 * @property mixed $metadata
 * @property string $text
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'user_id', 'url', 'text', 'moderator_id'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var string[]
     */
    protected $dates = ['created_at'];

    /**
     * Related to Laravel 5.8. deleted_at has different date format that created_at and carbon throws exception
     *
     * @var string[]
     */
    protected $casts = ['deleted_at' => 'string'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('Coyote\Flag\Type');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Coyote\User')->withTrashed();
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'resource', 'flag_resources');
    }

    public function topics()
    {
        return $this->morphedByMany(Topic::class, 'resource', 'flag_resources');
    }

    public function forums()
    {
        return $this->morphedByMany(Forum::class, 'resource', 'flag_resources');
    }

    public function microblogs()
    {
        return $this->morphedByMany(Microblog::class, 'resource', 'flag_resources');
    }

    public function jobs()
    {
        return $this->morphedByMany(Job::class, 'resource', 'flag_resources');
    }

    public function comments()
    {
        return $this->morphedByMany(Job\Comment::class, 'resource', 'flag_resources');
    }
}
