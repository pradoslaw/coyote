<?php

class InvoiceEnumeratorTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Coyote\User
     */
    protected $user;

    /**
     * @var \Coyote\Services\Invoice\Enumerator
     */
    private $enumerator;

    protected function _before()
    {
        $repository = app(\Coyote\Repositories\Contracts\InvoiceRepositoryInterface::class);
        $this->enumerator = new \Coyote\Services\Invoice\Enumerator($repository);

        $this->user = $this->tester->createUser();
    }

    // tests
    public function testEnumerateFirstInvoice()
    {
        $faker = Faker\Factory::create();

        $invoice = $this->tester->haveRecord(\Coyote\Invoice::class, ['name' => $faker->company, 'user_id' => $this->user->id]);

        $this->enumerator->enumerate($invoice);
        $date = \Carbon\Carbon::today();

        $this->assertEquals($this->format($date, 1), $invoice->number);
    }

    public function testEnumerateMultipleInvoice()
    {
        $faker = Faker\Factory::create();
        $date = \Carbon\Carbon::today();

        $this->tester->haveRecord(\Coyote\Invoice::class, ['name' => $faker->company, 'number' => $this->format($date, 1),  'user_id' => $this->user->id]);
        $invoice = $this->tester->haveRecord(\Coyote\Invoice::class, ['name' => $faker->company, 'user_id' => $this->user->id]);

        $this->enumerator->enumerate($invoice);

        $this->assertEquals($this->format($date, 2), $invoice->number);
    }

    private function format($date, $seq)
    {
        return sprintf('%02d%02d%02d-%d', $date->year, $date->month, $date->day, $seq);
    }
}
