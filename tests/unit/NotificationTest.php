<?php

use Coyote\Notification;
use Coyote\User;

class NotificationTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $user;
    protected $author;

    protected function _before()
    {
        $this->user = User::where('name', 'admin')->first();
    }

    protected function _after()
    {
    }

    private function create()
    {
        $objectId = rand(100, 9999);

        $notification = Notification::create([
            'type_id' => Notification::MICROBLOG_LOGIN,
            'user_id' => $this->user->id,
            'subject' => 'Lorem ipsum',
            'excerpt' => 'excerpt',
            'url' => '/',
            'object_id' => $objectId
        ]);
        Notification\Sender::create(['notification_id' => $notification->id, 'user_id' => $this->user->id, 'name' => $this->user->name]);

        return $notification;
    }

    private function getUser($id)
    {
        return $this->tester->grabRecord('Coyote\User', ['id' => $id]);
    }

    // tests
    public function testNotificationCreation()
    {
        $before = $this->getUser($this->user->id);
        $notification = $this->create();

        $this->tester->seeRecord('notifications', ['object_id' => $notification->object_id]);
        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->notifications + 1, $after->notifications);
        $this->assertEquals($before->notifications_unread + 1, $after->notifications_unread);
    }

    public function testMarkAsRead()
    {
        $before = $this->getUser($this->user->id);
        $notification = $this->create();

        $notification->read_at = Carbon\Carbon::now();
        $notification->save();

        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->notifications + 1, $after->notifications);
        $this->assertEquals($before->notifications_unread, $after->notifications_unread);
    }

    public function testNotificationDelete()
    {
        $before = $this->getUser($this->user->id);

        $notification = $this->create();
        $notification->delete();

        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->notifications, $after->notifications);
        $this->assertEquals($before->notifications_unread, $after->notifications_unread);

        $this->tester->dontSeeRecord('notifications', ['object_id' => $notification->object_id]);
    }
}
