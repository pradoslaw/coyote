<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Illuminate\Database\Connection;

class WordRepository extends Repository implements WordRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Word';
    }

    public function allWords()
    {
        return $this->app[Connection::class]->table('words')->get();
    }
}
