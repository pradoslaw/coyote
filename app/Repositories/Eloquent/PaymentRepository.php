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
    public function ongoingPayments()
    {
        return $this
            ->model
            ->select(['days', 'job_id', 'ends_at'])
            ->where('ends_at', '>', Carbon::now())
            ->with(['job' => function (BelongsTo $builder) {
                return $builder->whereNull('deleted_at'); // shouldn't laravel do this for us? anyway, no deleted offers!
            }])
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
                'invoice_id'
            ])
            ->with(['job', 'invoice']);
    }
}
