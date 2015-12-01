<?php

use Coyote\Alert;
use Coyote\User;

class AlertTest extends \Codeception\TestCase\Test
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

        $alert = Alert::create([
            'type_id' => Alert::MICROBLOG,
            'user_id' => $this->user->id,
            'subject' => 'Lorem ipsum',
            'excerpt' => 'excerpt',
            'url' => '/',
            'object_id' => $objectId
        ]);
        Alert\Sender::create(['alert_id' => $alert->id, 'user_id' => $this->user->id, 'name' => $this->user->name]);

        return $alert;
    }

    // tests
    public function testAlertCreation()
    {
        $alert = $this->create();

        $this->tester->seeRecord('alerts', ['object_id' => $alert->object_id]);
        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);

        $this->assertEquals(1, $user->alerts);
        $this->assertEquals(1, $user->alerts_unread);
    }

    public function testMarkAsRead()
    {
        $alert = $this->create();

        $alert->read_at = Carbon\Carbon::now();
        $alert->save();

        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);

        $this->assertEquals(1, $user->alerts);
        $this->assertEquals(0, $user->alerts_unread);
    }

    public function testAlertDelete()
    {
        $alert = $this->create();
        $alert->delete();

        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);

        $this->assertEquals(0, $user->alerts);
        $this->assertEquals(0, $user->alerts_unread);

        $this->tester->dontSeeRecord('alerts', ['object_id' => $alert->object_id]);
    }
}