<?php

namespace Coyote\User;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'user_relations';

    /**
     * @var string[]
     */
    protected $fillable = ['is_blocked', 'related_user_id'];
}
