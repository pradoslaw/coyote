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
 * @property Job $job
 * @property Plan $plan
 * @property Invoice $invoice
 * @property int $coupon_id
 * @property Coupon $coupon
 * @property string $session_id
 */
class Payment extends Model
{
    const NEW = 1;
    const PENDING = 2;
    const PAID = 3;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['plan_id', 'status_id', 'days', 'starts_at', 'ends_at', 'coupon_id'];

    /**
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $attributes = ['status_id' => self::NEW];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            $payment->id = Uuid\Uuid::uuid4();
        });
    }

    /**
     * @return array
     */
    public static function getPaymentStatusesList()
    {
        return [Payment::NEW => 'Nowy', Payment::PENDING => 'W trakcie realizacji', Payment::PAID => 'Zapłacono'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job()
    {
        return $this->belongsTo(Job::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
