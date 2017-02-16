<?php

namespace Coyote\Repositories\Criteria;

use Coyote\Repositories\Contracts\RepositoryInterface as Repository;

class HasPermission extends Criteria
{
    /**
     * @var string
     */
    protected $permission;

    /**
     * @param string $permission
     */
    public function __construct(string $permission)
    {
        $this->permission = $permission;
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
                ->where('name', $this->permission)
                ->where('value', 1);
        });
    }
}
