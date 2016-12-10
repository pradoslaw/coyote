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
            ],
            $data
        );
        $id = $this->haveRecord('users', array_merge($data, ['password' => bcrypt($data['password'])]));
        return User::find($id);
    }
}
