<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\WordRepositoryInterface;
use Cache;

class WordRepository extends Repository implements WordRepositoryInterface
{
    /**
     * @return \Coyote\Word
     */
    public function model()
    {
        return 'Coyote\Word';
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return Cache::rememberForever('words', function () use ($columns) {
            return $this->model->get($columns);
        });
    }
}
