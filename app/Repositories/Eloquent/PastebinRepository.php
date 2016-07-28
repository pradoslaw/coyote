<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface;

class PastebinRepository extends Repository implements PastebinRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Pastebin';
    }

    public function purge()
    {
        $result = $this->model->whereNotNull('expires')->get();

        foreach ($result as $row) {
            $createdAt = new Carbon($row->created_at);
            $createdAt->addHour($row->expires);

            if (Carbon::now() > $createdAt) {
                $row->delete();
            }
        }
    }
}
