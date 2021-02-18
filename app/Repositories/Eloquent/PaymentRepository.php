<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Payment;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;

class PaymentRepository extends Repository implements PaymentRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Payment::class;
    }

    /**
     * @inheritdoc
     */
    public function ongoingPaymentsWithBoostBenefit()
    {
        return $this
            ->model
            ->select(['days', 'job_id', 'ends_at', 'payments.plan_id'])
            ->where('ends_at', '>', Carbon::now())
            ->join('jobs', 'jobs.id', '=', 'job_id')
            ->with('job')
            ->whereNull('deleted_at')
            ->where('is_boost', true)
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function filter()
    {
        return $this
            ->model
            ->select([
                'payments.id',
                'payments.created_at',
                'status_id',
                'job_id',
                'invoice_id',
                'users.name AS user_name'
            ])
            ->join('jobs', 'jobs.id', '=', 'job_id')
            ->join('users', 'users.id', '=', 'jobs.user_id')
            ->leftJoin('invoices', 'invoices.id', '=', 'invoice_id')
            ->with(['job', 'invoice']);
    }
}
