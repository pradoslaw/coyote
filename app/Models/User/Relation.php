<?php

namespace Coyote\User;

use Coyote\User;
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

    public function relatedUser()
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'photo', 'deleted_at', 'is_blocked']);
    }

    public function scopeBlocked(Builder $builder)
    {
        return $builder->where('is_blocked', true);
    }
}
