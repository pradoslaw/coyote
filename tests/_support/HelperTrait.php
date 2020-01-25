<?php

use Faker\Factory;
use Coyote\User;
use Coyote\Group;
use Coyote\Permission;

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
        User::unguard();

        return $this->haveRecord(User::class, array_merge($data, ['password' => bcrypt($data['password'])]));
    }

    public function createForum($attributes = [])
    {
        $fake = Factory::create();

        $data = [
            'name' => $name = $fake->name,
            'slug' => str_slug($name),
            'description' => $fake->text,
            'is_prohibited' => 0
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
            'user_id' => null,
            'created_at' => \Carbon\Carbon::now()
        ];

        return \Coyote\Post::forceCreate(array_merge($data, $attributes));
    }

    public function grantAdminAccess(User $user)
    {
        $fake = Factory::create();
        /** @var Group $admin */
        $admin = Group::forceCreate(['name' => $fake->name]);

        // assign user to the group
        $admin->users()->attach($user->id);

        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $admin->permissions()->attach($permission->id, ['value' => 1]);
        }
    }
}
