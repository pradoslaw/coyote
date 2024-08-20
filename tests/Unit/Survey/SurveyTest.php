<?php
namespace Tests\Unit\Survey;

use Coyote\Domain\Survey\Survey;
use Coyote\Services\Guest;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel\Application;

class SurveyTest extends TestCase
{
    use Application;

    private string $guestId;
    private Guest $guest;
    private Survey $survey;
    private MemoryClock $clock;

    #[Before]
    public function createGuestForSurvey(): void
    {
        $this->guestId = $this->randomUuid();
        $this->guest = new Guest($this->guestId);
        $this->laravel->app->instance(Guest::class, $this->guest);
        $this->clock = new MemoryClock();
        $this->survey = new Survey($this->guest, $this->clock);
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
    public function changeSurveyChoice(): void
    {
        $this->survey->setChoice('modern');
        $this->assertSame('modern', $this->survey->choice());
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
        $this->assertGuestSetting('survey-invited', 'surveyState');
    }

    #[Test]
    public function passSurveyStateToView_surveyInvited(): void
    {
        $this->survey->setState('survey-invited');
        $this->assertSame('survey-invited', $this->surveyViewInput()['surveyState']);
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

    #[Test]
    public function initiallyUserChoiceIsNone(): void
    {
        $this->assertSame('none', $this->survey->choice());
    }

    #[Test]
    public function userChoosesModern(): void
    {
        $this->guest->setSetting('postCommentStyle', 'modern');
        $this->assertSame('modern', $this->survey->choice());
    }

    #[Test]
    public function userChoosesLegacy(): void
    {
        $this->guest->setSetting('postCommentStyle', 'legacy');
        $this->assertSame('legacy', $this->survey->choice());
    }

    #[Test]
    public function rejectMalformedValue(): void
    {
        $this->guest->setSetting('postCommentStyle', 'boo hoo');
        $this->assertSame('none', $this->survey->choice());
    }

    #[Test]
    public function passSurveyExperimentToView_modern(): void
    {
        $this->guest->setSetting('postCommentStyle', 'modern');
        $this->assertSame('modern', $this->surveyViewInput()['surveyChoice']);
    }

    #[Test]
    public function returnOnlyValidStates(): void
    {
        $this->survey->setState('foo-bar');
        $this->assertSame('survey-none', $this->survey->state());
    }

    #[Test]
    public function storeInvalidStateInSettings_forFutureReuse(): void
    {
        $this->survey->setState('foo-bar');
        $this->assertGuestSetting('foo-bar', 'surveyState');
    }

    #[Test]
    #[TestWith([
        'survey-none', 'survey-invited', 'survey-declined',
        'survey-accepted', 'survey-instructed', 'survey-gone',
    ])]
    public function acceptValidSurveyStates(string $state): void
    {
        $this->survey->setState($state);
        $this->assertSame($state, $this->survey->state());
    }

    #[Test]
    public function storeSurveyStateByHttp(): void
    {
        $this->laravel->post('/survey', ['surveyState' => 'survey-accepted'])->assertSuccessful();
        $this->assertSame('survey-accepted', $this->survey->state());
    }

    #[Test]
    public function storeSurveyChoiceByHttp(): void
    {
        $this->laravel->post('/survey', ['surveyChoice' => 'modern'])->assertSuccessful();
        $this->assertSame('modern', $this->survey->choice());
    }

    #[Test]
    public function rejectRequestWithoutArguments(): void
    {
        $response = $this->laravel->post('/survey');
        $this->assertSame(422, $response->status());
    }

    #[Test]
    public function logStateChange(): void
    {
        $this->survey->setState('survey-accepted');
        $this->assertSurveyLogValues(['survey-accepted']);
    }

    #[Test]
    public function logMultipleStateChanges(): void
    {
        $this->survey->setState('survey-accepted');
        $this->survey->setState('survey-declined');
        $this->assertSurveyLogValues(['survey-accepted', 'survey-declined']);
    }

    private function assertSurveyLogValues(array $expectedValue): void
    {
        $this->assertSame($expectedValue, \array_column($this->guestSettings('surveyLog'), 1));
    }

    #[Test]
    public function logTime(): void
    {
        $this->clock->setTime('2024');
        $this->survey->setState('survey-accepted');
        $this->assertGuestSetting([['2024', 'survey-accepted']], 'surveyLog');
    }

    private function assertGuestSetting(string|array $expectedValue, string $settingName): void
    {
        $this->assertSame($expectedValue, $this->guestSettings($settingName));
    }

    private function guestSettings(string $key): array|string
    {
        $jsonSettings = $this->laravel->databaseTable('guests')
            ->where('id', $this->guestId)
            ->first('settings')
            ->settings;
        return \json_decode($jsonSettings, associative:true)[$key];
    }
}
