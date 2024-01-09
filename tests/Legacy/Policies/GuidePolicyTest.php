<?php

namespace Tests\Legacy\Policies;

use Coyote\Guide;
use Coyote\Policies\GuidePolicy;
use Coyote\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GuidePolicyTest extends TestCase
{
    use WithFaker;

    private User $user;
    private Guide $guide;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->make(['id' => $this->faker->numberBetween()]);
        $this->guide = factory(Guide::class)->make(['id' => $this->faker->numberBetween()]);
    }

    public function testUpdateAllowedByAuthor()
    {
        $policy = new GuidePolicy();
        $this->assertTrue($policy->update($this->guide->user, $this->guide));
    }

    public function testUpdateNotAllowedByAnyOtherUser()
    {
        $policy = new GuidePolicy();
        $this->assertFalse($policy->update($this->user, $this->guide));
    }

    public function testUpdateAllowedByAdmin()
    {
        Gate::define('guide-update', function () {
            return true;
        });

        $policy = new GuidePolicy();
        $this->assertTrue($policy->update($this->user, $this->guide));
    }
}
