<?php

use Coyote\Notification;
use Coyote\User;
use Ramsey\Uuid\Uuid;

class NotificationTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $user;
    protected $author;

    protected $sender;

    /**
     * @var User
     */
    protected $recipient;

    protected function _before()
    {
        $this->user = User::where('name', 'admin')->first();

        $this->sender = $this->tester->createUser();
        $this->recipient = $this->tester->createUser();
    }

    protected function _after()
    {
    }

    public function testNotifyUserMentionedInMicroblog()
    {
        $text = "Hello @{$this->recipient->name}";
        /** @var Coyote\Microblog $microblog */
        $microblog = $this->tester->haveRecord(Coyote\Microblog::class, ['user_id' => $this->sender->id, 'text' => $text]);

        $notification = new \Coyote\Notifications\Microblog\UserMentionedNotification($microblog);
        $this->recipient->notify($notification);

        $this->tester->seeRecord('notifications', ['user_id' => $this->recipient->id, 'subject' => $text, 'object_id' => $this->getObjectId($notification, $microblog)]);

        // hit again...
        $text = "Hello again @{$this->recipient->name}";
        /** @var Coyote\Microblog $microblog */
        $microblog = $this->tester->haveRecord(Coyote\Microblog::class, ['user_id' => $this->sender->id, 'text' => $text]);

        $notification = new \Coyote\Notifications\Microblog\UserMentionedNotification($microblog);
        $this->recipient->notify($notification);

        $this->tester->seeRecord('notifications', ['user_id' => $this->recipient->id, 'subject' => $text, 'object_id' => $this->getObjectId($notification, $microblog)]);
    }

    public function testPmNotification()
    {
        $text = Faker\Factory::create()->text(20);

        $setting = $this->recipient->notificationSettings()->where('type_id', Notification::PM)->first();
        $setting->profile = true; // enable notification
        $setting->save();

        $repository = app(\Coyote\Repositories\Contracts\PmRepositoryInterface::class);
        $pm = $repository->submit($this->sender, ['author_id' => $this->recipient->id, 'text' => $text]);

        $notification = new \Coyote\Notifications\PmCreatedNotification($pm[\Coyote\Pm::INBOX]);
        $message = $notification->toBroadcast();

        $this->tester->assertEquals($this->sender->name . ' przesyła Ci nową wiadomość', $message->data['headline']);
        $this->tester->assertEquals($this->sender->id, $notification->sender()['user_id']);

        $this->recipient->notify($notification);

        $this->tester->seeRecord('notifications', ['type_id' => Notification::PM, 'user_id' => $this->recipient->id, 'subject' => $text]);
    }

    public function testMergeMicroblogNotifications()
    {
        /** @var Coyote\Microblog $parent */
        $parent = $this->tester->haveRecord(Coyote\Microblog::class, ['user_id' => $this->sender->id, 'text' => Faker\Factory::create()->text]);

        $text1 = "Hello";
        /** @var Coyote\Microblog $microblog */
        $microblog = $this->tester->haveRecord(Coyote\Microblog::class, ['user_id' => $this->sender->id, 'text' => $text1, 'parent_id' => $parent->id]);

        $notification = new \Coyote\Notifications\Microblog\SubmittedNotification($microblog);
        $this->recipient->notify($notification);

        $this->tester->seeRecord('notifications', ['user_id' => $this->recipient->id, 'excerpt' => $text1]);

        // hit again
        $text2 = "Hello v2";
        /** @var Coyote\Microblog $microblog */
        $microblog = $this->tester->haveRecord(Coyote\Microblog::class, ['user_id' => $this->sender->id, 'text' => $text2, 'parent_id' => $parent->id]);

        $notification = new \Coyote\Notifications\Microblog\SubmittedNotification($microblog);
        $this->recipient->notify($notification);


        $this->tester->dontSeeRecord('notifications', ['user_id' => $this->recipient->id, 'excerpt' => $text2]);
    }

    private function getObjectId($notification, $model)
    {
        return substr(md5(class_basename($notification) . $model->id), 16);
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

    private function create()
    {
        $objectId = rand(100, 9999);

        $notification = Notification::create([
            'id' => Uuid::uuid4(),
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
}
