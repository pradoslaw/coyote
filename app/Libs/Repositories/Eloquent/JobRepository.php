<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\JobRepositoryInterface;

class JobRepository extends Repository implements JobRepositoryInterface
{
    /**
     * @return \Coyote\Job
     */
    public function model()
    {
        return 'Coyote\Job';
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById($id)
    {
        $this->applyCriteria();

        return $this->model
                    ->select(['jobs.*', 'countries.name AS country_name', 'currencies.name AS currency_name'])
                    ->leftJoin('countries', 'countries.id', '=', 'country_id')
                    ->leftJoin('currencies', 'currencies.id', '=', 'currency_id')
                    ->findOrFail($id);
    }
}
