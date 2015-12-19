<?php

namespace Coyote\Topic;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['topic_id', 'tag_id'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'topic_tags';

    /**
     * @var array
     */
    public $timestamps = false;

    public function tag()
    {
        return $this->hasOne('Coyote\Tag');
    }
}
