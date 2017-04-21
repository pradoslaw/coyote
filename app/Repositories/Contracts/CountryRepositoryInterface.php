<?php

namespace Coyote\Repositories\Contracts;

interface CountryRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array
     */
    public function vatRatesList();
}
