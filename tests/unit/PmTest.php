<?php

use Coyote\Pm;
use Coyote\User;
use Faker\Factory;

class PmTest extends \Codeception\TestCase\Test
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

        $fake = Factory::create();
        $password = bcrypt('password');

        $this->author = User::create(['name' => $fake->userName, 'email' => $fake->email, 'password' => $password]);
    }

    protected function _after()
    {
    }

    private function getUser($id)
    {
        return $this->tester->grabRecord('Coyote\User', ['id' => $id]);
    }

    private function create()
    {
        $rootId = rand(100, 9999);

        $text = Pm\Text::create(['text' => 'Lorem ipsum lores']);
        // new message from $this->author to $this->user
        $pm = Pm::create(['root_id' => $rootId, 'user_id' => $this->user->id, 'author_id' => $this->author->id, 'folder' => Pm::INBOX, 'text_id' => $text->id]);
        Pm::create(['root_id' => $rootId, 'user_id' => $this->author->id, 'author_id' => $this->user->id, 'folder' => Pm::SENTBOX, 'text_id' => $text->id]);

        return $pm;
    }

    // tests
    public function testPrivateMessageCreation()
    {
        $before = $this->getUser($this->user->id);
        $pm = $this->create();

        $this->tester->seeRecord('pm', ['root_id' => $pm->root_id]);
        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->pm + 1, $after->pm);
        $this->assertEquals($before->pm_unread + 1, $after->pm_unread);
    }

    public function testMarkAsRead()
    {
        $before = $this->getUser($this->user->id);
        $pm = $this->create();

        $pm->read_at = Carbon\Carbon::now();
        $pm->save();

        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->pm + 1, $after->pm);
        $this->assertEquals($before->pm_unread, $after->pm_unread);
    }

    public function testPrivateMessageDelete()
    {
        $before = $this->getUser($this->user->id);
        $pm = $this->create();
        $pm->delete();

        $after = $this->getUser($this->user->id);

        $this->assertEquals($before->pm, $after->pm);
        $this->assertEquals($before->pm_unread, $after->pm_unread);

        // jeden rekord powinien byc w bazie danych...
        $this->tester->seeRecord('pm', ['root_id' => $pm->root_id]);
    }

    public function testCompleteRemovingFromDatabase()
    {
        $pm = $this->create();
        $pm->delete();

        // jeden rekord powinien byc w bazie danych...
        $this->tester->seeRecord('pm_text', ['id' => $pm->text_id]);

        Pm::where('root_id', $pm->root_id)->delete();
        $this->tester->dontSeeRecord('pm_text', ['id' => $pm->text_id]);
    }
}