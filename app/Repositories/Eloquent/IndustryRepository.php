<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Industry;
use Coyote\Repositories\Contracts\IndustryRepositoryInterface;

class IndustryRepository extends Repository implements IndustryRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Industry::class;
    }

    /**
     * @return array
     */
    public function getAlphabeticalList(): array
    {
        return $this->model->select('id', 'name')->orderBy('name')->pluck('name', 'id')->toArray();
    }
}
