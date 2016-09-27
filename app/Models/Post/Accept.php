<?php

namespace Coyote\Post;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $post_id
 * @property int $user_id
 * @property int $topic_id
 * @property string $ip
 * @property \Coyote\Post $post
 */
class Accept extends Model
{
    use ForUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'user_id', 'topic_id', 'ip'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_accepts';

    /**
     * @var array
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function post()
    {
        return $this->belongsTo('Coyote\Post');
    }
}
