<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class HasPermission extends Criteria
{
    /**
     * @var string[]
     */
    protected array $permissions;

    /**
     * @param string[] $permissions
     */
    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param Repository $repository
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model, Repository $repository)
    {
        return $model->whereIn('users.id', function ($builder) {
            return $builder
                ->select('user_id')
                ->from('permissions')
                ->join('group_permissions', 'group_permissions.permission_id', '=', 'permissions.id')
                ->join('group_users', 'group_users.group_id', '=', 'group_permissions.group_id')
                ->whereIn('name', $this->permissions)
                ->where('value', 1);
        });
    }
}
