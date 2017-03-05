<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Invoice;
use Coyote\Repositories\Contracts\InvoiceRepositoryInterface;

class InvoiceRepository extends Repository implements InvoiceRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Invoice::class;
    }
}
