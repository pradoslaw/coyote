<?php

namespace Coyote\Post;

use Illuminate\Database\Eloquent\Model;
use Coyote\Post;

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

        $previous = static::where('post_id', $this->post_id)->latest()->limit(1)->first();
        if (!$previous) {
            return true;
        }

        $isDirty = false;
        $compare = ['tags', 'subject', 'tags', 'text'];

        foreach ($compare as $column) {
            if ($previous->$column !== $this->$column) {
                $isDirty = true;
                break;
            }
        }

        return $isDirty;
    }
}
