<?php

namespace Coyote;

use Coyote\Post\Attachment;
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
 * @property string $created_at
 * @property string $deleted_at
 * @property string $updated_at
 * @property string $user_name
 * @property string $text
 * @property string $ip
 * @property string $browser
 * @property string $host
 * @property \Coyote\Forum $forum
 * @property \Coyote\Topic $topic
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
     * Elasticsearch type mapping
     *
     * @var array
     */
    protected $mapping = [
        "tags" => [
            "type" => "multi_field",
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
        "user_name" => [
            "type" => "string",
            // ability to search case insensitive
            "analyzer" => "analyzer_keyword"
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
        return $this->hasMany('Coyote\Post\Subscriber');
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
     * Assign attachments to the post
     *
     * @param array $attachments
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments()->update(['post_id' => null]);
        $rows = [];

        foreach ($attachments as $attachment) {
            $rows[] = Attachment::where('file', $attachment['file'])->first();
        }

        $this->attachments()->saveMany($rows);
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
     * Return data to index in elasticsearch
     *
     * @return array
     */
    protected function getIndexBody()
    {
        $body = $this->parentGetIndexBody();
        
        // additionally index few fields from topics table...
        $topic = $this->topic()->first(['subject', 'slug', 'forum_id', 'id', 'first_post_id']);
        // we need to index every field from posts except:
        $body = array_except($body, ['deleted_at', 'edit_count', 'editor_id']);

        if ($topic->first_post_id == $body['id']) {
            $body['tags'] = $topic->tags()->lists('name');
        }

        return array_merge($body, [
            'topic' => $topic,
            'forum' => $this->forum()->first(['name', 'slug'])
        ]);
    }
}
