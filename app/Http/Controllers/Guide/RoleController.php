<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Guide;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function update(Guide $guide, Request $request): string
    {
        $this->validate(
            $request,
            ['role' => ['required', Rule::in([Guide\Role::JUNIOR, Guide\Role::MID, Guide\Role::SENIOR])]]
        );

        $role = $guide->roles()->forUser($this->userId)->firstOrNew();

        $reflectionClass = new \ReflectionClass(Guide\Role::class);
        $chosenRole = strtolower(
            array_flip($reflectionClass->getConstants(\ReflectionClassConstant::IS_PUBLIC))[$request->input('role')]
        );

        $role->role = $chosenRole;

        $role->user_id = $this->userId;
        $role->save();

        $guide->role = $this->calculateRole($guide);
        $guide->save();

        return $chosenRole;
    }

    private function calculateRole(Guide $guide): string
    {
        $roles = array_count_values($guide->roles()->get(['role'])->pluck('role')->toArray());
        arsort($roles);

        return key($roles);
    }
}
