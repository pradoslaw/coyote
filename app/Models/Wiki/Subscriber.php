<?php

namespace Coyote\Wiki;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use ForUser;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wiki_subscribers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wiki_id', 'user_id'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
