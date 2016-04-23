<?php

namespace Coyote;

use Coyote\Elasticsearch\Elasticsearch;
use Coyote\Post\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes, Elasticsearch;

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
            $rows[] = Attachment::where('file', $attachment)->first();
        }

        $this->attachments()->saveMany($rows);
    }

    /**
     * Return data to index in elasticsearch
     *
     * @return array
     */
    protected function getIndexBody()
    {
        // additionally index few fields from topics table...
        $topic = $this->topic()->first(['subject', 'path', 'forum_id', 'id', 'first_post_id']);
        // we need to index every field from posts except:
        $body = array_except($this->toArray(), ['deleted_at', 'edit_count', 'editor_id']);

        if ($topic->first_post_id == $body['id']) {
            $body['tags'] = $topic->tags()->lists('name');
        }

        foreach (['created_at', 'updated_at'] as $column) {
            if (!empty($body[$column])) {
                $body[$column] = date('Y-m-d H:i:s', strtotime($body[$column]));
            }
        }

        return array_merge($body, [
            'topic' => $topic,
            'forum' => $this->forum()->first(['name', 'path'])
        ]);
    }
}
