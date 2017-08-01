<?php

namespace Coyote\Repositories\Contracts;

interface IndustryRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array
     */
    public function getAlphabeticalList(): array;
}
