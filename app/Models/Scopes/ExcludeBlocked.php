<?php


namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ExcludeBlocked
{
    public function scopeExcludeBlocked(Builder $builder, ?int $userId)
    {
        if (!$userId) {
            return $builder;
        }

        $relation = $this->table === 'users' ? "{$this->table}.id" : "{$this->table}.user_id";

        return $builder->where($relation, '!=', $userId)->whereNotExists(function ($builder) use ($userId, $relation) {
            return $builder
                ->select('user_relations.id')
                ->from('user_relations')
                ->whereRaw("user_relations.user_id  = $relation")
                ->where('related_user_id', $userId)
                ->where('is_blocked', true);
        });
    }
}
