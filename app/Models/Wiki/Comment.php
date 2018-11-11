<?php

namespace Coyote\Wiki;

use Coyote\Models\Scopes\ForUser;
use Coyote\User;
use Coyote\Wiki;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $wiki_id
 * @property int $user_id
 * @property string $text
 * @property string $ip
 * @property string $html
 * @property Wiki $wiki
 * @property User $user
 */
class Comment extends Model
{
    use SoftDeletes, ForUser;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wiki_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wiki_id', 'user_id', 'text', 'ip'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wiki()
    {
        return $this->belongsTo(Wiki::class);
    }

    /**
     * @param int $wikiId
     * @param int $userId
     * @return bool
     */
    public function wasUserInvolved($wikiId, $userId)
    {
        return $this->where('wiki_id', $wikiId)->forUser($userId)->exists();
    }

    /**
     * @param string $html
     */
    public function setHtmlAttribute($html)
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getHtmlAttribute()
    {
        if ($this->html !== null) {
            return $this->html;
        }

        return $this->html = app('parser.wiki')->parse($this->text);
    }
}
