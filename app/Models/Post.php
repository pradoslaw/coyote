<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Post extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'forum_id', 'user_id', 'user_name', 'text', 'ip', 'browser', 'host'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public function scopeDistinctOn($query)
    {echo DB::raw('DISTINCT ON(posts.id)');
        return $query->select(DB::raw('DISTINCT ON(posts.id)'));
    }
}
