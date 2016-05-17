<?php

namespace Coyote\Repositories\Contracts;

interface PastebinRepositoryInterface extends RepositoryInterface
{
    public function purge();
}
