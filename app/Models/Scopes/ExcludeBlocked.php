<?php


namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait ExcludeBlocked
{
    public function scopeExcludeBlocked(Builder $builder, ... $usersId)
    {
        if (!$usersId) {
            return $builder;
        }

        $table = $builder->getModel()->getTable();
        $relation = $table === 'users' ? "{$table}.id" : "{$table}.user_id";

        return $builder->where($relation, '!=', $usersId[0])->whereNotExists(function ($builder) use ($usersId, $relation) {
            return $builder
                ->select('user_relations.id')
                ->from('user_relations')
                ->whereRaw("user_relations.user_id  = $relation")
                ->whereIn('related_user_id', $usersId)
                ->where('is_blocked', true);
        });
    }
}
