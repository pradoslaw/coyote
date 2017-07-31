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
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public function pluck($value, $key = null)
    {
        $this->model->orderBy('name');

        return parent::pluck($value, $key);
    }
}
