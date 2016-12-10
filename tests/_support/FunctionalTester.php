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
    use HelperTrait;

    /**
     * Define custom actions here
     */

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
            'slug' => str_slug($name),
            'description' => $fake->text
        ];

        $id = $this->haveRecord('forums', array_merge($data, $attributes));
        return $this->grabRecord('Coyote\Forum', ['id' => $id]);
    }

    public function createTopic($attributes)
    {
        $fake = Factory::create();

        $data = [
            'subject' => $name = $fake->name,
            'slug' => str_slug($name)
        ];

        \Coyote\Topic::unguard();

        return $this->haveRecord('Coyote\Topic', array_merge($data, $attributes));
    }

    public function createPost($attributes)
    {
        $fake = Factory::create();

        $data = [
            'text' => $fake->text,
            'ip' => $fake->ipv4,
            'browser' => $fake->userAgent,
            'host' => $fake->domainName,
            'user_id' => null
        ];

        \Coyote\Post::unguard();

        return $this->haveRecord('Coyote\Post', array_merge($data, $attributes));
    }
}
