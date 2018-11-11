<?php

namespace Coyote\Wiki;

use Coyote\Models\Scopes\ForUser;
use Coyote\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $wiki_id
 * @property int $user_id
 * @property int $parent_id
 * @property int $length
 * @property int $diff
 * @property string $title
 * @property string $path
 * @property string $slug
 * @property string $excerpt
 * @property string $text
 * @property string $comment
 * @property string $ip
 * @property string $browser
 * @property string $host
 * @property \Carbon\Carbon $created_at
 * @property User $user
 */
class Log extends Model
{
    use ForUser;

    /**
     * @var string
     */
    protected $table = 'wiki_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wiki_id', 'parent_id', 'user_id', 'title', 'excerpt', 'text', 'comment', 'ip', 'host', 'browser', 'length', 'diff'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
