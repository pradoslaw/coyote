<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\WordRepositoryInterface;

class WordRepository extends Repository implements WordRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Word';
    }
}
