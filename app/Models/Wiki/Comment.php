<?php

namespace Coyote\Wiki;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $wiki_id
 * @property int $user_id
 * @property string $text
 * @property string $ip
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Coyote\User');
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
}
