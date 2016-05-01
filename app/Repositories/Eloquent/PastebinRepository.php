<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PastebinRepositoryInterface;

class PastebinRepository extends Repository implements PastebinRepositoryInterface
{
    /**
     * @return \Coyote\Pastebin
     */
    public function model()
    {
        return 'Coyote\Pastebin';
    }
}
