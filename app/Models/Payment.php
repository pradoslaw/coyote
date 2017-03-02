<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid;

/**
 * @property string $id
 * @property int $job_id
 * @property int $plan_id
 * @property int $status_id
 * @property int $days
 * @property int $invoice_id
 * @property \Carbon\Carbon $starts_at
 * @property \Carbon\Carbon $ends_at
 */
class Payment extends Model
{
    const PENDING = 1;
    const PAID = 2;

    /**
     * @var bool
     */
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            $payment->id = Uuid\Uuid::uuid4();
        });
    }
}
