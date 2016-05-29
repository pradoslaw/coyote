<?php

namespace Coyote\Http\Factories;

use Coyote\Repositories\Contracts\FlagRepositoryInterface as FlagRepository;

trait FlagFactory
{
    /**
     * @return FlagRepository
     */
    protected function getFlagFactory()
    {
        return app(FlagRepository::class);
    }
}
