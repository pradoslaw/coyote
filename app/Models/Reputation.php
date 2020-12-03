<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Reputation extends Model
{
    const POST_VOTE = 1;
    const POST_ACCEPT = 2;
    const MICROBLOG = 3;
    const MICROBLOG_VOTE = 4;
    const WIKI_CREATE = 5;
    const WIKI_UPDATE = 6;
    const CUSTOM = 7;
    const WIKI_RATE = 8;

    const USING_CHINESE = 1;
    const URL_IN_PROFILE = 50;
    const URL_IN_SIG = 50;
    const ADDING_TAGS = 300;
    const DELETING_POSTS = 300;
    const POSTING_SHORT_TITLE = 1000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'user_id', 'value', 'excerpt', 'url', 'metadata'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function getMetadataAttribute($metadata)
    {
        return json_decode($metadata, true);
    }

    public function setMetadataAttribute($metadata)
    {
        $this->attributes['metadata'] = json_encode($metadata);
    }
}
