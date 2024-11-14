<?php
namespace Tests\Unit\Topic;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Forum;
use Tests\Unit\BaseFixture\Server;
use Tests\Unit\BaseFixture\Server\Laravel;

class CreateTopicTest extends TestCase
{
    use Laravel\Application;
    use Laravel\Transactional;
    use Server\Http;
    use Forum\Models;

    #[Test]
    public function createsTopic_asNewUser(): void
    {
        $this->loginAsUser();
        $response = $this->laravel->post('/Forum/Newbie/Submit', [
            'title' => 'One two three',
            'text'  => 'content',
        ]);
        $response->assertCreated();
        $this->laravel->assertSeeInDatabase('topics', [
            'title' => 'One two three',
        ]);
    }

    private function loginAsUser(): void
    {
        $this->server->loginById($this->models->newUserReturnId());
    }
}
