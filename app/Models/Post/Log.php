<?php

namespace Coyote\Post;

use Illuminate\Database\Eloquent\Model;
use Coyote\Post;

/**
 * @property int $post_id
 * @property int $user_id
 * @property string $text
 * @property string $subject
 * @property array $tags
 * @property string $comment
 * @property string $ip
 * @property string $browser
 */
class Log extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'user_id', 'text', 'subject', 'tags', 'comment', 'ip', 'browser'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_log';

    /**
     * @var array
     */
    public $timestamps = false;

    protected $casts = ['tags' => 'json'];

    /**
     * @param $tags
     */
    public function setTagsAttribute($tags)
    {
        $this->attributes['tags'] = json_encode($tags);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getTagsAttribute($value)
    {
        return json_decode($value);
    }
}
