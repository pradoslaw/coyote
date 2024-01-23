<?php
namespace Tests\Unit\OpenGraph;

use Coyote\Currency;
use Coyote\Job;
use Coyote\Plan;
use Coyote\User;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class JobApplicationTest extends TestCase
{
    use BaseFixture\Server\RelativeUri;
    use BaseFixture\Server\Laravel\Transactional;
    use Fixture\OpenGraph;

    /**
     * @test
     */
    public function title()
    {
        $job = $this->newJobOffer('Banana offer');
        $this->assertThat(
            $this->ogProperty('og:title', uri:"/Praca/Application/$job->id"),
            $this->identicalTo("Aplikuj na stanowisko Banana offer :: 4programmers.net"));
    }

    private function newJobOffer(string $title): Job
    {
        $currency = new Currency();
        $currency->name = 'irrelevant';
        $currency->save();

        $plan = new Plan;
        $plan->name = 'irrelevant';
        $plan->save();

        $user = new User;
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();

        $job = new Job;
        $job->slug = 'irrelevant';
        $job->title = $title;
        $job->user_id = $user->id;
        $job->plan_id = $plan->id;
        $job->currency_id = $currency->id;
        $job->save();
        return $job;
    }
}
