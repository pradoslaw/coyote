<?php

namespace Coyote\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function scopeBlocked(Builder $query)
    {
        return $query->where('is_blocked', true);
    }
}
