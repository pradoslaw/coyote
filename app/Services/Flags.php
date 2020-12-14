<?php

namespace Coyote\Services;

use Coyote\Flag;
use Illuminate\Contracts\Auth\Access\Gate;

class Flags
{
    private Gate $gate;
    private array $models = [];
    private array $permission = [];

    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    public function fromModels(array $models)
    {
        $this->models = $models;

        return $this;
    }

    public function permission(string $ability, array $arguments = [])
    {
        $this->permission = [$ability, $arguments];

        return $this;
    }

    public function get()
    {
        if (!$this->gate->allows(...$this->permission)) {
            return [];
        }

        $builder = Flag::with(['resources', 'user:id,name', 'type']);

        foreach ($this->models as $model) {
            $builder = $builder->orHas(str_plural(strtolower(class_basename($model))));
        }

        return $builder->get();
    }
}
