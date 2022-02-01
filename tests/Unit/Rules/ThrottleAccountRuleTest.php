<?php

namespace Tests\Unit\Rules;

use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Rules\ThrottleAccountRule;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\TestCase;

class ThrottleAccountRuleTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testDoNotAllowUserToCreateAnotherAccount()
    {
        $ip = $this->faker->ipv4;

        factory(User::class)->create(['created_at' => now()->subHour(), 'ip' => $ip]);

        $request = $this->partialMock(Request::class, function (MockInterface $mock) use ($ip) {
            $mock->shouldReceive('ip')->andReturn($ip);
        });

        $rule = new ThrottleAccountRule($this->app[UserRepositoryInterface::class], $request);
        $this->assertFalse($rule->passes('foo', 'does not matter'));
    }

    public function testAllowUserToCreateAnotherAccountDespiteHavingIdenticalIpInDb()
    {
        $ip = $this->faker->ipv4;

        factory(User::class)->create(['created_at' => now()->subMonth(), 'ip' => $ip]);

        $request = $this->partialMock(Request::class, function (MockInterface $mock) use ($ip) {
            $mock->shouldReceive('ip')->andReturn($ip);
        });

        $rule = new ThrottleAccountRule($this->app[UserRepositoryInterface::class], $request);
        $this->assertTrue($rule->passes('foo', 'does not matter'));
    }
}
