<?php
namespace Coyote\Services;

use Coyote\Flag;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent;

class Flags
{
    private array $models = [];
    private array $permission = [];

    public function __construct(private Gate $gate)
    {
    }

    public function fromModels(array $models): self
    {
        $this->models = $models;
        return $this;
    }

    public function permission(string $ability, array $arguments = []): self
    {
        $this->permission = [$ability, $arguments];
        return $this;
    }

    public function get(): array|Eloquent\Collection
    {
        if (!$this->hasAccess()) {
            return [];
        }
        $builder = Flag::with(['resources', 'user:id,name', 'type']);
        foreach ($this->models as $model) {
            $builder = $builder->orHas($this->relationName($model));
        }
        return $builder->get();
    }

    private function hasAccess(): bool
    {
        if (empty($this->permission)) {
            return true;
        }
        return $this->gate->allows(...$this->permission);
    }

    private function relationName(string $model): string
    {
        return str_plural(\strToLower(class_basename($model)));
    }
}
