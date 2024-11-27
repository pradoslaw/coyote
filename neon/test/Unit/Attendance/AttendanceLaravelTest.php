<?php
namespace Neon\Test\Unit\Attendance;

use Illuminate\Database\DatabaseManager;
use Neon\Laravel\Attendance;
use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture\Server\Laravel\Application;

class AttendanceLaravelTest extends TestCase
{
    use Application;

    /**
     * @test
     */
    public function totalUsers(): void
    {
        $database = $this->database();
        $usersTable = $database->table('users');
        $users = $usersTable->count();
        $id = $usersTable->insertGetId(['name' => uniqid(), 'email' => '']);
        $attendance = (new Attendance($database))->fetchAttendance();
        $usersTable->delete($id);
        $this->assertSame($users + 1, $attendance->totalUsers);
    }

    private function database(): DatabaseManager
    {
        /** @var DatabaseManager $database */
        $database = $this->laravel->app->get(DatabaseManager::class);
        return $database;
    }
}
