<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    const PM = 1;
    const POST_SUBMIT = 2;
    const TOPIC_MOVE = 3;
    const TOPIC_DELETE = 4;
    const POST_DELETE = 5;
    const POST_COMMENT = 6;
    const PAGE = 7;
    const COMMENT = 8;
    const POST_EDIT = 10;
    const TOPIC_SUBJECT = 11;
    const TOPIC_SOLVED = 12;
    const POST_COMMENT_LOGIN = 13;
    const POST_LOGIN = 14;
    const MICROBLOG = 15;
    const MICROBLOG_LOGIN = 16;
    const POST_VOTE = 18;
    const MICROBLOG_VOTE = 19;
    const MICROBLOG_WATCH = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'user_id', 'subject', 'excerpt', 'url', 'object_id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function type()
    {
        return $this->hasOne('Coyote\Alert\Type', 'id', 'type_id');
    }

    public function senders()
    {
        return $this->hasMany('Coyote\Alert\Sender')->leftJoin('users', 'users.id', '=', 'alert_senders.user_id');
    }
}
