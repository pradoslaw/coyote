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

    /**
     * @var string[]
     */
    protected $fillable = ['is_blocked', 'related_user_id'];

    public function scopeBlocked(Builder $builder)
    {
        return $builder->where('is_blocked', true);
    }
}
