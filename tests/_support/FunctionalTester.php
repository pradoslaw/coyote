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

        $data = [
            'name'       => $fake->name,
            'email'      => $fake->email,
            'password'   => $fake->password,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];

        $id = $this->haveRecord('users', array_merge($data, ['password' => bcrypt($data['password'])]));
        return User::find($id);
    }

    public function logInAsRandomUser()
    {
        $user = User::first();
        $this->amLoggedAs($user);

        return $user;
    }
    
    public function logInAsAdmin()
    {
        $user = User::where('name', 'admin')->first();
        $this->amLoggedAs($user);
        
        return $user;
    }

    public function createForum($attributes = [])
    {
        $fake = Factory::create();

        $data = [
            'name' => $name = $fake->name,
            'path' => str_slug($name),
            'description' => $fake->text
        ];
        
        $id = $this->haveRecord('forums', array_merge($data, $attributes));
        return $this->grabRecord('forums', ['id' => $id]);
    }
}
