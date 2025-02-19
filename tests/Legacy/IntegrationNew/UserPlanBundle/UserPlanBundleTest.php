<?php
namespace Tests\Legacy\IntegrationNew\UserPlanBundle;

use Coyote\Models\UserPlanBundle;
use Coyote\Payment;
use Coyote\Plan;
use Coyote\User;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;

class UserPlanBundleTest extends TestCase
{
    use Laravel\Transactional;

    #[Test]
    #[DoesNotPerformAssertions]
    public function createUserPlanBundle(): void
    {
        $bundle = new UserPlanBundle();
        $bundle->remaining = 20;
        $bundle->user()->associate(factory(User::class)->create());
        $bundle->plan()->associate(Plan::query()->firstOrFail());
        $bundle->payment()->associate(Payment::query()->firstOrFail());
        $bundle->save();
    }

    #[Test]
    public function planDoesNotContainsBundle(): void
    {
        $plan = new Plan();
        $plan->name = 'no bundle';
        $plan->save();
        $this->assertNull($plan->bundle_size);
    }

    #[Test]
    public function planContainsBundle(): void
    {
        $plan = new Plan();
        $plan->name = 'no bundle';
        $plan->bundle_size = 3;
        $plan->save();
        $this->assertSame(3, $plan->bundle_size);
    }
}
