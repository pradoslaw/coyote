<?php

namespace Tests\Feature\Resources;

use Coyote\Http\Resources\NotificationResource;
use Coyote\Notification;
use Faker\Factory;
use Tests\TestCase;

class NotificationResourceTest extends TestCase
{
    public function testNotificationSendersCount()
    {
        $faker = Factory::create();

        /** @var Notification $notification */
        $notification = factory(Notification::class)->state('headline')->make();

        $senders = [
            new \Coyote\Notification\Sender(['user_id' => factory(\Coyote\User::class)->make(), 'name' => $faker->userName]),
            new \Coyote\Notification\Sender(['user_id' => factory(\Coyote\User::class)->make(), 'name' => $faker->userName]),
            new \Coyote\Notification\Sender(['user_id' => factory(\Coyote\User::class)->make(), 'name' => $faker->userName]),
        ];

        $notification->setRelation('senders', collect($senders));

        $resource = new NotificationResource($notification);
        $result = $resource->toArray(request());

        $this->assertStringContainsString($senders[0]->name . ' (oraz 2 osoby)', $result['headline']);

        $senders = [
            new \Coyote\Notification\Sender(['user_id' => factory(\Coyote\User::class)->make(), 'name' => $faker->userName]),
            new \Coyote\Notification\Sender(['user_id' => factory(\Coyote\User::class)->make(), 'name' => $faker->userName]),
        ];

        $notification->setRelation('senders', collect($senders));

        $resource = new NotificationResource($notification);
        $result = $resource->toArray(request());

        $this->assertStringContainsString($senders[0]->name . ' (oraz ' . $senders[1]->name . ')', $result['headline']);

        $senders = [
            new \Coyote\Notification\Sender(['user_id' => factory(\Coyote\User::class)->make(), 'name' => $faker->userName])
        ];

        $notification->setRelation('senders', collect($senders));

        $resource = new NotificationResource($notification);
        $result = $resource->toArray(request());

        $this->assertStringContainsString($senders[0]->name, $result['headline']);
    }
}
