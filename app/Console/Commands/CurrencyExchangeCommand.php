<?php

namespace Coyote\Console\Commands;

use Coyote\Repositories\Contracts\CurrencyRepositoryInterface as CurrencyRepository;
use Illuminate\Console\Command;

class CurrencyExchangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:exchange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get latest currency exchange rates.';

    /**
     * @var CurrencyRepository
     */
    private $currency;

    /**
     * @param CurrencyRepository $currency
     */
    public function __construct(CurrencyRepository $currency)
    {
        parent::__construct();

        $this->currency = $currency;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $swap = app('swap');

        /** @var \Coyote\Currency $currency */
        foreach ($this->currency->all() as $currency) {
            if ($currency->name === 'PLN') {
                continue;
            }

            $rate = $swap->latest("PLN/$currency->name");

            $currency->exchanges()->updateOrCreate(
                ['date' => $rate->getDate()->format('Y-m-d'), 'currency_id' => $currency->id],
                ['value' => str_replace(',', '.', $rate->getValue())]
            );

            $this->info('Adding ... ' . $currency->name);
        }

        $this->info('Done.');

        return 0;
    }
}
