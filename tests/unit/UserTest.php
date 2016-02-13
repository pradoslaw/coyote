<?php

use Coyote\User;

class UserTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testUserRegistration()
    {
        $name = 'Joe Doe';
        $email = 'johndoe@example.com';
        $password = bcrypt('password');

        User::create(['name' => $name, 'email' => $email, 'password' => $password]);

        $this->tester->seeRecord('users', ['name' => $name, 'email' => $email, 'password' => $password]);
    }

    public function testIfModelReturnsAdminUserEmail()
    {
        // access model
        $user = User::where('name', 'admin')->first();
        $this->assertEquals('admin@4programmers.net', $user->email);
    }
}