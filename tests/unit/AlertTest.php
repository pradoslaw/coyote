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

    private function getUser($id)
    {
        return $this->tester->grabRecord('Coyote\User', ['id' => $id]);
    }

    // tests
    public function testAlertCreation()
    {
        $before = $this->getUser($this->user->id);
        $alert = $this->create();

        $this->tester->seeRecord('alerts', ['object_id' => $alert->object_id]);
        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->alerts + 1, $after->alerts);
        $this->assertEquals($before->alerts_unread + 1, $after->alerts_unread);
    }

    public function testMarkAsRead()
    {
        $before = $this->getUser($this->user->id);
        $alert = $this->create();

        $alert->read_at = Carbon\Carbon::now();
        $alert->save();

        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->alerts + 1, $after->alerts);
        $this->assertEquals($before->alerts_unread, $after->alerts_unread);
    }

    public function testAlertDelete()
    {
        $before = $this->getUser($this->user->id);
        $alert = $this->create();
        $alert->delete();

        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->alerts, $after->alerts);
        $this->assertEquals($before->alerts_unread, $after->alerts_unread);

        $this->tester->dontSeeRecord('alerts', ['object_id' => $alert->object_id]);
    }
}