<?php

use Coyote\User;

use Faker\Factory;

class UserTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    protected function _before()
    {
        $this->faker = Factory::create();
    }

    // tests
    public function testUserRegistration()
    {
        $name = 'Joe Doe';
        $email = 'johndoe@example.com';
        $password = bcrypt('password');

        User::forceCreate(['name' => $name, 'email' => $email, 'password' => $password]);

        $this->tester->seeRecord('users', ['name' => $name, 'email' => $email, 'password' => $password]);
    }

    public function testIfModelReturnsAdminUserEmail()
    {
        // access model
        $user = User::where('name', 'admin')->first();
        $this->assertEquals('admin@4programmers.net', $user->email);
    }

    public function testSendSuccessfulLoginEmailCauseEmptyStream()
    {
        $mailer = Mockery::mock(Illuminate\Contracts\Mail\Mailer::class);
        $mailer->shouldReceive('send');

        $listener = $this->listener($mailer);

        $user = factory(User::class)->create(['visits' => 1, 'ip' => $this->faker->ipv4]);

        $this->event($listener, $user);
    }

    public function testDontSendSuccessfulLoginEmail()
    {
        $mailer = Mockery::mock(Illuminate\Contracts\Mail\Mailer::class);
        $mailer->shouldNotReceive('send');

        $listener = $this->listener($mailer);

        $user = factory(User::class)->create();

        $this->event($listener, $user);
    }

    public function testDontSendSuccessfulLoginEmailCauseExistsInStream()
    {
        $mailer = Mockery::mock(Illuminate\Contracts\Mail\Mailer::class);
        $mailer->shouldNotReceive('send');

        $listener = $this->listener($mailer);
        $user = factory(User::class)->create(['ip' => $this->faker->ipv4]);

        $browser = $this->faker->userAgent;

        $this->tester->haveRecord('streams', ['verb' => 'login', 'ip' => $this->faker->ipv4, 'browser' => $browser, 'actor' => '{"id": ' . $user->id . '}']);

        $event = new \Coyote\Events\SuccessfulLogin($user, $this->faker->ipv4, $browser);
        $listener->handle($event);
    }

    private function listener($mailer)
    {
        return new \Coyote\Listeners\SendSuccessfulLoginEmail(
            $mailer,
            app(\Coyote\Repositories\Contracts\StreamRepositoryInterface::class),
            new \Jenssegers\Agent\Agent()
        );
    }

    private function event($listener, $user)
    {
        $event = new \Coyote\Events\SuccessfulLogin($user, $this->faker->ipv4, $this->faker->userAgent);
        $listener->handle($event);
    }
}
