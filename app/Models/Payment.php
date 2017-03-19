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
    protected $fillable = ['plan_id', 'status_id', 'days', 'starts_at', 'ends_at'];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'starts_at', 'ends_at'];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
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
     * @return float
     */
    public function netPrice()
    {
        return $this->plan->price * $this->days;
    }

    /**
     * @return float
     */
    public function grossPrice()
    {
        return $this->netPrice() * $this->plan->vat_rate;
    }

    /**
     * @return float
     */
    public function vat()
    {
        return $this->grossPrice() - $this->netPrice();
    }

    /**
     * @return float
     */
    public function getNetPriceAttribute()
    {
        return $this->netPrice();
    }

    /**
     * @return float
     */
    public function getGrossPriceAttribute()
    {
        return $this->grossPrice();
    }

    /**
     * @return float
     */
    public function getVatAttribute()
    {
        return $this->vat();
    }
}
