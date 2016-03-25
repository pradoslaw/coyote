<?php

use Faker\Factory;
use Coyote\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Define custom actions here
     */

    public function createUser()
    {
        $fake = Factory::create();

        return $this->haveRecord('users', [
            'name'       => $fake->name,
            'email'      => $fake->email,
            'password'   => bcrypt($fake->password),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);
    }

    public function logInAsRandomUser()
    {
        $user = User::first();
        $this->amLoggedAs($user);

        return $user;
    }
}
