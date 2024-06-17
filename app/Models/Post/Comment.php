<?php

namespace Coyote\Post;

use Coyote\Post;
use Coyote\Services\Parser\Factories\CommentFactory;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $text
 * @property string $html
 * @property int $post_id
 * @property int $user_id
 * @property int $id
 * @property Post $post
 * @property User $user
 * @property \Carbon\Carbon $created_at
 */
class Comment extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'user_id', 'text'];

    /**
     * @var string[]
     */
    protected $appends = ['html'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_comments';

    /**
     * @var null|string
     */
    private $html = null;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at', 'reputation'])->withTrashed();
    }

    public function getHtmlAttribute(): string
    {
        if ($this->html === null) {
            $app = new CommentFactory(app(), $this->user_id);
            $this->html = $app->parse($this->text);
        }
        return $this->html;
    }
}
