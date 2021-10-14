<?php

namespace Coyote;

use Coyote\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property User $user
 * @property string $title
 * @property string $excerpt
 * @property string $text
 * @property Tag[] $tags
 * @property int $user_id
 * @property Comment[] $comments
 * @property Comment[] $commentsWithChildren
 * @property string $slug
 */
class Guide extends Model
{
    use Taggable;

    protected $fillable = ['title', 'excerpt', 'text'];

    public function getSlugAttribute(): string
    {
        return str_slug($this->title, '_');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscribers()
    {
        return $this->morphMany(Subscription::class, 'resource');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne(Page::class, 'content');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'resource', 'tag_resources');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'resource');
    }

    public function commentsWithChildren()
    {
        $userRelation = fn ($builder) => $builder->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked', 'is_online'])->withTrashed();

        return $this
            ->comments()
            ->whereNull('parent_id')
            ->orderBy('id', 'DESC')
            ->with([
                'children' => function ($builder) use ($userRelation) {
                    return $builder->orderBy('id')->with(['user' => $userRelation]);
                },
                'user' => $userRelation
            ]);
    }
}
