<?php

namespace Coyote\Post;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class History extends Model
{
    const INITIAL_SUBJECT = 1;
    const INITIAL_BODY = 2;
    const INITIAL_TAGS = 3;
    const EDIT_SUBJECT = 4;
    const EDIT_BODY = 5;
    const EDIT_TAGS = 6;
    const ROLLBACK_SUBJECT = 7;
    const ROLLBACK_BODY = 8;
    const ROLLBACK_TAGS = 9;
    const DELETE = 10;
    const RESTORE = 11;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'post_id', 'user_id', 'data', 'comment', 'guid'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_history';

    /**
     * @var array
     */
    public $timestamps = false;
}
