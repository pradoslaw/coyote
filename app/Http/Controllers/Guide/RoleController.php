<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Guide;
use Coyote\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function update(Guide $guide, Request $request)
    {
        $this->validate(
            $request,
            ['role' => ['required', Rule::in([Guide\Role::JUNIOR, Guide\Role::MIDDLE, Guide\Role::SENIOR])]]
        );

        $role = $guide->roles()->forUser($this->userId)->firstOrNew();

        $role->role = strtolower($request->input('role'));
        $role->user_id = $this->userId;
        $role->save();

        $guide->role = $this->calculateRole($guide);
        $guide->save();
    }

    private function calculateRole(Guide $guide): string
    {
        $roles = array_count_values($guide->roles()->get(['role'])->pluck('role')->toArray());
        arsort($roles);

        return key($roles);
    }
}
