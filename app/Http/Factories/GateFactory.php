<?php

namespace Coyote\Http\Factories;

use Illuminate\Contracts\Auth\Access\Gate;

trait GateFactory
{
    /**
     * @return Gate
     */
    protected function getGateFactory()
    {
        return app(Gate::class);
    }
}
