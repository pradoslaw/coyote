<?php

namespace Tests\Unit\Resources;

use Coyote\Http\Resources\UserResource;
use Coyote\User;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    public function testSimpleUserModelWithoutOptionalFields()
    {
        $user = factory(User::class)->make();

        $this->assertArrayNotHasKey('allow_sig', $user);
        $this->assertArrayNotHasKey('allow_count', $user);
        $this->assertArrayNotHasKey('sig', $user);

        $resource = (new UserResource($user))->resolve(request());

        $this->assertArrayNotHasKey('allow_sig', $resource);
        $this->assertArrayNotHasKey('allow_count', $user);
        $this->assertArrayNotHasKey('sig', $user);
    }

    public function testUserModelWithOptionalFields()
    {
        $user = factory(User::class)->make(['sig' => 'foo', 'allow_sig' => true]);

        $this->assertArrayHasKey('allow_sig', $user);
        $this->assertArrayHasKey('sig', $user);

        $resource = (new UserResource($user))->resolve(request());

        $this->assertArrayHasKey('allow_sig', $resource);
        $this->assertArrayHasKey('sig', $resource);

        $this->assertEquals($user->sig, $resource['sig']);
    }
}
