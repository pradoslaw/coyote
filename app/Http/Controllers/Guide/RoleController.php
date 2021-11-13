<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Guide;
use Coyote\Http\Controllers\Controller;
use Coyote\Services\Guide\RoleCalculator;
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

        $reflectionClass = new \ReflectionClass(Guide\Role::class);
        $role = strtolower(
            array_flip($reflectionClass->getConstants(\ReflectionClassConstant::IS_PUBLIC))[$request->input('role')]
        );

        (new RoleCalculator($guide))->setRole($this->userId, $role);
        $guide->save();

        return $role;
    }
}
