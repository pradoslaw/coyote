<?php

namespace Coyote\Microblog;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['microblog_id', 'name'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'microblog_tags';

    /**
     * @var array
     */
    public $timestamps = false;
}
