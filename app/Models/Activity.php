<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['created_at', 'forum_id', 'user_id', 'topic_id', 'content_type', 'content_id', 'excerpt', 'user_name'];

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
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function content()
    {
        return $this->morphTo()->withTrashed();
    }
}
