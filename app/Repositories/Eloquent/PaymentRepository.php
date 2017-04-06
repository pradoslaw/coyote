<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Payment;
use Coyote\Repositories\Contracts\PaymentRepositoryInterface;
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
    public function hasRecentlyPaid(int $userId, int $days = 7)
    {
        return $this
            ->model
            ->join('jobs', function (JoinClause $join) use ($userId) {
                return $join->on('jobs.id', '=', 'job_id')->on('user_id', $this->raw($userId));
            })
            ->where('payments.created_at', '>', Carbon::now()->subDay($days))
            ->where('status_id', Payment::PAID)
            ->exists();
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
                'invoice_id'
            ])
            ->with(['job', 'invoice']);
    }
}
