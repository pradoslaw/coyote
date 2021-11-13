<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property User $user
 * @property int $user_id
 * @property int $parent_id
 * @property string $email
 * @property string $text
 * @property \Coyote\Comment[]|\Illuminate\Support\Collection $children
 * @property Comment $parent
 * @property string $html
 * @property \Coyote\Job|\Coyote\Guide $resource
 * @property string $resource_type
 * @property int $resource_id
 */
class Comment extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = ['user_id', 'email', 'parent_id', 'text'];

    /**
     * @var string
     */
    protected $table = 'comments';

    /**
     * @var array
     */
    protected $appends = ['html'];

    /**
     * @var null|string
     */
    private $html = null;

    public function resource()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class);
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
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email ?: $this->user->email;
    }
}
