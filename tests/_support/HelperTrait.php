<?php

use Faker\Factory;
use Coyote\User;

trait HelperTrait
{
    public function createUser(array $data = [])
    {
        $fake = Factory::create();
        $data = array_merge(
            [
                'name'       => $fake->name,
                'email'      => $fake->email,
                'password'   => $fake->password,
                'created_at' => new \DateTime(),
                'updated_at' => new \DateTime(),
                'guest_id'   => \Faker\Provider\Uuid::uuid()
            ],
            $data
        );
        $id = $this->haveRecord('users', array_merge($data, ['password' => bcrypt($data['password'])]));
        return User::find($id);
    }

    public function createForum($attributes = [])
    {
        $fake = Factory::create();

        $data = [
            'name' => $name = $fake->name,
            'slug' => str_slug($name),
            'description' => $fake->text
        ];

        return \Coyote\Forum::forceCreate(array_merge($data, $attributes));
    }

    public function createTopic($attributes)
    {
        $fake = Factory::create();

        $data = [
            'subject' => $name = $fake->name,
            'slug' => str_slug($name)
        ];

        return \Coyote\Topic::forceCreate(array_merge($data, $attributes));
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

        return \Coyote\Post::forceCreate(array_merge($data, $attributes));
    }
}
