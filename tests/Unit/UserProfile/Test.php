<?php
namespace Tests\Unit\UserProfile;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Server\Http;
    use BaseFixture\Forum\Store;
    use Fixture\UserProfile;

    /**
     * @test
     */
    public function statistics(): void
    {
        $response = $this->userProfile($this->userWithPost());
        $this->assertSame(
            [
                'Postów: 1',
                'Komentarzy: 0',
                'Głosów oddanych: 0',
                'Głosów otrzymanych: 0',
                'Wpisów na mikroblogu: 0',
                'Odpowiedzi zaakceptowanych: 0',
            ],
            $this->userStatistics($response));
    }

    /**
     * @test
     */
    public function microblogs(): void
    {
        $response = $this->userProfile($this->userWithMicroblog());
        $this->assertContains(
            'Wpisów na mikroblogu: 1',
            $this->userStatistics($response));
    }

    /**
     * @test
     */
    public function acceptedAnswers(): void
    {
        $response = $this->userProfile($this->userWithAcceptedAnswer());
        $this->assertContains(
            'Odpowiedzi zaakceptowanych: 1',
            $this->userStatistics($response));
    }
}
