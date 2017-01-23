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
 * @property string $host
 */
class Log extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'user_id', 'text', 'subject', 'tags', 'comment', 'ip', 'browser', 'host'];

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

    /**
     * @param Post $post
     * @return $this
     */
    public function fillWithPost(Post $post)
    {
        $this->fill($post->toArray());
        $this->post_id = $post->id;

        return $this;
    }

    /**
     * Determine if post was somehow changed comparing to previous version
     *
     * @return bool
     */
    public function isDirtyComparedToPrevious()
    {
        if (empty($this->post_id)) {
            return true;
        }

        /** @var Log $previous */
        $previous = static::where('post_id', $this->post_id)->latest()->limit(1)->first();
        if (!$previous) {
            return true;
        }

        $diff = array_merge(
            array_diff($this->tags, (array) $previous->tags),
            array_diff((array) $previous->tags, $this->tags)
        );

        if (!empty($diff)) {
            return true;
        }

        $isDirty = false;

        foreach (['subject', 'text'] as $column) {
            if ($previous->{$column} !== $this->{$column}) {
                $isDirty = true;
                break;
            }
        }

        return $isDirty;
    }
}
