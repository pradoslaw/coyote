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

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->app['cache']->rememberForever('words', function () use ($columns) {
            return $this->model->get($columns);
        });
    }
}
