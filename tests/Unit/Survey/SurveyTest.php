<?php
namespace Tests\Unit\Survey;

use Coyote\Domain\Survey\Survey;
use Coyote\Services\Guest;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel\Application;

class SurveyTest extends TestCase
{
    use Application;

    private string $guestId;
    private Survey $survey;

    #[Before]
    public function createGuestForSurvey(): void
    {
        $this->guestId = $this->randomUuid();
        $guest = new Guest($this->guestId);
        $this->laravel->app->instance(Guest::class, $guest);
        $this->survey = new Survey($guest);
    }

    private function randomUuid(): string
    {
        $data = \random_bytes(16);
        return \vsPrintF('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($data), 4));
    }

    #[Test]
    public function inviteUserToSurvey(): void
    {
        $this->survey->setState('survey-invited');
        $this->assertSame('survey-invited', $this->survey->state());
    }

    #[Test]
    public function initialSurveyState(): void
    {
        $this->assertSame('survey-none', $this->survey->state());
    }

    #[Test]
    public function storeInvitation_inGuestSettings(): void
    {
        $this->survey->setState('survey-invited');
        $this->assertGuestSettings(['surveyState' => 'survey-invited']);
    }

    #[Test]
    public function passSurveyStateToView_surveyInvited(): void
    {
        $this->survey->setState('survey-invited');
        $this->assertSame(['surveyState' => 'survey-invited'], $this->surveyViewInput());
    }

    #[Test]
    public function inLayoutExistsScriptJson(): void
    {
        $this->assertTrue($this->viewDom()->exists('//script[@type="application/json"]'));
    }

    #[Test]
    public function scriptJsonHasSurveyId(): void
    {
        $this->assertSame('survey', $this->viewDom()->findString('//script[@type="application/json"]/@id'));
    }

    private function assertGuestSettings(array $expectedSettings): void
    {
        $this->laravel->assertSeeInDatabase('guests', [
            'id'       => $this->guestId,
            'settings' => \json_encode($expectedSettings),
        ]);
    }

    private function surveyViewInput(): array
    {
        return \json_decode($this->surveyViewInputJson(), associative:true);
    }

    private function surveyViewInputJson(): string
    {
        return $this->viewDom()->findString('//script[@type="application/json"]/text()');
    }

    private function viewDom(): ViewDom
    {
        return new ViewDom($this->laravel->get('/')->assertSuccessful()->content());
    }
}
