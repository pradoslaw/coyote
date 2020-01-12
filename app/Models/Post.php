<?php

namespace Coyote;

use Coyote\Post\Subscriber;
use Coyote\Services\Elasticsearch\CharFilters\PostFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int $forum_id
 * @property int $topic_id
 * @property int $score
 * @property int $edit_count
 * @property int $editor_id
 * @property int $deleter_id
 * @property string $delete_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 * @property \Carbon\Carbon $updated_at
 * @property string $user_name
 * @property string $text
 * @property string $html
 * @property string $ip
 * @property string $browser
 * @property string $host
 * @property \Coyote\Forum $forum
 * @property \Coyote\Topic $topic
 * @property \Coyote\Post\Attachment[] $attachments
 * @property \Coyote\Post\Vote[] $votes
 * @property \Coyote\Post\Comment[] $comments
 * @property \Coyote\User $user
 * @property \Coyote\Post\Accept $accept
 */
class Post extends Model
{
    use SoftDeletes;
    use Searchable {
        getIndexBody as parentGetIndexBody;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'forum_id', 'user_id', 'user_name', 'text', 'ip', 'browser', 'host', 'edit_count', 'editor_id'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Elasticsearch type mapping
     *
     * @var array
     */
    protected $mapping = [
        "tags" => [
            "type" => "string",
            "fields" => [
                "tag" => [
                    "type" => "string"
                ],

                // original value (case sensitive)
                "tag_original" => [
                    "type" => "string",
                    "index" => "not_analyzed"
                ]
            ]
        ],
        "text" => [
            "type" => "string",
            "analyzer" => "default_analyzer"
        ],
        "user_name" => [
            "type" => "string",
            // ability to search case insensitive
            "analyzer" => "keyword_analyzer"
        ],
        "ip" => [
            "type" => "string",
            "index" => "not_analyzed"
        ],
        "host" => [
            "type" => "string",
            "index" => "not_analyzed"
        ],
        "created_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
        "updated_at" => [
            "type" => "date",
            "format" => "yyyy-MM-dd HH:mm:ss"
        ],
    ];

    /**
     * Html version of the post.
     *
     * @var null|string
     */
    private $html = null;

    public static function boot()
    {
        parent::boot();

        static::restoring(function (Post $post) {
            $post->deleter_id = null;
            $post->delete_reason = null;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Coyote\Post\Comment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers()
    {
        return $this->hasMany(Subscriber::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('Coyote\Post\Attachment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany('Coyote\Post\Vote');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accept()
    {
        return $this->hasOne('Coyote\Post\Accept');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo('Coyote\Topic');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forum()
    {
        return $this->belongsTo('Coyote\Forum');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Coyote\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany('Coyote\Post\Log');
    }

    /**
     * Enable/disable subscription for this post
     *
     * @param int $userId
     * @param bool $flag
     */
    public function subscribe($userId, $flag)
    {
        if (!$flag) {
            $this->subscribers()->where('user_id', $userId)->delete();
        } else {
            $this->subscribers()->firstOrCreate(['post_id' => $this->id, 'user_id' => $userId]);
        }
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
     * @param array $ids
     */
    public function syncAttachments($ids)
    {
        foreach ($this->attachments as $attachment) {
            $attachment->post()->dissociate()->save();
        }

        foreach ($ids as $id) {
            Post\Attachment::find($id)->post()->associate($this)->save();
        }
    }

    /**
     * Get previous post. We use it in merge controller.
     *
     * @return mixed
     */
    public function previous()
    {
        return (new static)
            ->where('topic_id', $this->topic_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * @param int $userId
     * @param string|null $reason
     */
    public function deleteWithReason(int $userId, ?string $reason)
    {
        $this->deleter_id = $userId;
        $this->delete_reason = $reason;
        $this->{$this->getDeletedAtColumn()} = $this->freshTimestamp();

        $this->save();
    }

    /**
     * Return data to index in elasticsearch
     *
     * @return array
     */
    protected function getIndexBody()
    {
        // topic removed? skip indexing
        if (!$this->topic) {
            return [];
        }

        $this->setCharFilter(PostFilter::class);
        $body = $this->parentGetIndexBody();

        // additionally index few fields from topics table...
        $topic = $this->topic->only(['subject', 'slug', 'forum_id', 'id', 'first_post_id']);
        // we need to index every field from posts except:
        $body = array_except($body, ['deleted_at', 'edit_count', 'editor_id', 'delete_reason', 'deleter_id', 'user']);

        if ($topic['first_post_id'] == $body['id']) {
            $body['tags'] = $this->topic->tags()->pluck('name');
        }

        return array_merge($body, [
            'topic' => $topic,
            'forum' => $this->forum->only(['name', 'slug'])
        ]);
    }
}
