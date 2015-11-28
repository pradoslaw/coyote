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

    private function create()
    {
        $rootId = rand(100, 9999);

        $text = Pm\Text::create(['text' => 'Lorem ipsum lores']);
        $pm = Pm::create(['root_id' => $rootId, 'user_id' => $this->user->id, 'author_id' => $this->author->id, 'folder' => Pm::INBOX, 'text_id' => $text->id]);
        Pm::create(['root_id' => $rootId, 'user_id' => $this->author->id, 'author_id' => $this->user->id, 'folder' => Pm::SENTBOX, 'text_id' => $text->id]);

        return $pm;
    }

    // tests
    public function testPrivateMessageCreation()
    {
        $pm = $this->create();

        $this->tester->seeRecord('pm', ['root_id' => $pm->root_id]);
        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);

        $this->assertEquals(1, $user->pm);
        $this->assertEquals(1, $user->pm_unread);
    }

    public function testMarkAsRead()
    {
        $pm = $this->create();

        $pm->read_at = Carbon\Carbon::now();
        $pm->save();

        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);

        $this->assertEquals(1, $user->pm);
        $this->assertEquals(0, $user->pm_unread);
    }

    public function testPrivateMessageDelete()
    {
        $pm = $this->create();
        $pm->delete();

        $user = $this->tester->grabRecord('users', ['id' => $this->user->id]);

        $this->assertEquals(0, $user->pm);
        $this->assertEquals(0, $user->pm_unread);

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