<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\CouponRepositoryInterface;
use Illuminate\Console\Command;

class CreateCouponCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupon:create {--count=} {--amount=} {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create coupon(s)';

    /**
     * @var CouponRepositoryInterface
     */
    protected $coupon;

    /**
     * @param CouponRepositoryInterface $coupon
     */
    public function __construct(CouponRepositoryInterface $coupon)
    {
        parent::__construct();

        $this->coupon = $coupon;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for ($i = 0; $i < $this->option('count'); $i++) {
            $this->coupon->create(
                ['code' => $code = str_random(5), 'amount' => $this->option('amount'), 'user_id' => $this->hasOption('user') ? $this->option('user') : null]
            );

            $this->line($code);
        }
    }
}
