<?php
namespace Tests\Unit\Survey;

use Carbon\Carbon;
use Coyote\Models\Survey;
use Illuminate\Contracts\Session\Session;
use Neon\Test\BaseFixture\Selector\Css;
use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Administration\Fixture\AdministratorPanel;
use Tests\Unit\BaseFixture\Forum\ModelsDsl;
use Tests\Unit\BaseFixture\Server\Laravel\Transactional;

class AdmTest extends TestCase
{
    use AdministratorPanel;
    use Transactional;

    private ModelsDsl $dsl;

    #[Before]
    public function initialize(): void
    {
        $this->dsl = new ModelsDsl();
    }

    #[Before]
    public function administratorDashboard(): void
    {
        $this->userInAdministratorDashboard();
    }

    #[Before]
    public function assumeDatabaseIsEmpty(): void
    {
        Survey::query()->delete();
    }

    #[Test]
    public function hasExperimentSidemenuLabel(): void
    {
        $this->assertContains('Eksperymenty', $this->sidemenuLabels());
    }

    #[Test]
    public function sidemenuLabelLinksToExperimentsRoute(): void
    {
        $this->assertStringEndsWith(
            '/Adm/Experiments',
            $this->sidemenuHrefByLabel('Eksperymenty'));
    }

    #[Test]
    public function sidemenuLabelHasExperimentIcon(): void
    {
        $this->assertStringContainsString(
            'fa-solid fa-flask',
            $this->sidemenuIconByLabel('Eksperymenty'));
    }

    #[Test]
    public function sidemenuPageExists(): void
    {
        $this->laravel->get('/Adm/Experiments')->assertSuccessful();
    }

    #[Test]
    public function experimentsPageRendersView(): void
    {
        $view = $this->experimentsDom();
        $this->assertTrue($view->exists('//main'));
        $this->assertTrue($view->exists('//aside/ul'));
        $this->assertSame('Eksperymenty :: 4programmers.net', $view->findString('//title/text()'));
    }

    #[Test]
    public function experimentsBreadcrumbs(): void
    {
        $this->assertContains('Eksperymenty', $this->breadcrumbs($this->experimentsDom()));
    }

    #[Test]
    public function newExperimentBreadcrumbs(): void
    {
        $breadcrumbs = $this->breadcrumbs($this->newExperimentDom());
        $this->assertContains('Eksperymenty', $breadcrumbs);
        $this->assertContains('Nowy', $breadcrumbs);
    }

    #[Test]
    public function existingExperimentBreadcrumbs(): void
    {
        $id = $this->newSurveyReturnId(surveyTitle:'foo');
        $breadcrumbs = $this->breadcrumbs($this->experimentDom($id));
        $this->assertContains('Eksperymenty', $breadcrumbs);
        $this->assertContains('foo', $breadcrumbs);
    }

    #[Test]
    public function experimentBreadcrumbUrl(): void
    {
        $view = $this->experimentDom($this->newSurveyReturnId());
        $this->assertStringEndsWith('/Adm/Experiments',
            $this->breadcrumbsUrl($view, $this->breadcrumbsIndexOf($view, 'Eksperymenty')));
    }

    #[Test]
    public function addLinkToNewExperiment(): void
    {
        $link = $this->experimentsDom()->findString('//main/article//a/@href');
        $this->assertStringEndsWith('/Adm/Experiments/Save', $link);
    }

    #[Test]
    public function addLinkBackToExperiments(): void
    {
        $link = $this->newExperimentDom()->findString('//main/article//a/@href');
        $this->assertStringEndsWith('/Adm/Experiments', $link);
    }

    private function breadcrumbs(ViewDom $view): array
    {
        return \array_map(\trim(...), $view->findStrings("//ul[@class='breadcrumb']/li/*[1]/text()"));
    }

    private function breadcrumbsIndexOf(ViewDom $view, string $title): int
    {
        return \array_search($title, $this->breadcrumbs($view));
    }

    private function breadcrumbsUrl(ViewDom $view, int $index): string
    {
        return $view->findStrings("//ul[@class='breadcrumb']/li/a[1]/@href")[$index];
    }

    private function sidemenuIconByLabel(string $label): string
    {
        return $this->dashboardDom()->findString("//ul/li/a[text()='$label']/i/@class");
    }

    private function sidemenuHrefByLabel(string $label): string
    {
        return $this->dashboardDom()->findString("//ul/li/a[text()='$label']/@href");
    }

    private function sidemenuLabels(): array
    {
        return $this->dashboardDom()->findStrings('//ul/li/a/text()');
    }

    private function dashboardDom(): ViewDom
    {
        return $this->dom('/Adm/Dashboard');
    }

    private function experimentsDom(): ViewDom
    {
        return $this->dom('/Adm/Experiments');
    }

    private function newExperimentDom(): ViewDom
    {
        return $this->dom('/Adm/Experiments/Save');
    }

    private function dom(string $uri): ViewDom
    {
        return new ViewDom($this->server->get($uri)->assertSuccessful()->content());
    }

    #[Test]
    public function listSurveysNone(): void
    {
        $this->assertSame(['Nie ma jeszcze żadnych eksperymentów.'],
            $this->experimentsListText());
    }

    #[Test]
    public function listSurveysMany(): void
    {
        $this->newSurvey('Foo');
        $this->newSurvey('Bar');
        $this->assertSame(['Foo', 'Bar'], $this->experimentsListText());
    }

    #[Test]
    public function showSurvey(): void
    {
        $id = $this->newSurveyReturnId('Foo');
        $this->assertSame('Eksperyment: Foo', $this->experimentTitle($id));
    }

    #[Test]
    public function showSurveyWithLink(): void
    {
        $id = $this->newSurveyReturnId('Foo');
        $this->assertStringEndsWith("/Adm/Experiments/$id", $this->experimentsListHref());
    }

    private function experimentsListText(): array
    {
        $card = new Css('.card');
        return $this->normalized($this->experimentsDom()->findStrings("//article/$card/div[2]//text()"));
    }

    private function experimentsListHref(): string
    {
        $card = new Css('.card');
        return \trim($this->experimentsDom()->findString("//article/$card//a/@href"));
    }

    private function newSurvey(string $title): void
    {
        Survey::query()->create(['title' => $title]);
    }

    private function newSurveyReturnId(string $surveyTitle = null, Carbon $createdAt = null, array $usersId = null): int
    {
        /** @var Survey $survey */
        $survey = Survey::query()->create([
            'title'      => $surveyTitle ?? '',
            'created_at' => $createdAt,
        ]);
        $survey->users()->sync($usersId);
        return $survey->id;
    }

    private function normalized(array $strings): array
    {
        return \array_values(\array_filter(\array_map(\trim(...), $strings), strLen(...)));
    }

    #[Test]
    public function showUsersAssignedToSurveyNone(): void
    {
        $this->assertSame('0', $this->experimentUsers($this->newSurveyReturnId('Foo')));
    }

    #[Test]
    public function showSurveyCreationDate(): void
    {
        // make sure showing survey also displays creation date
        $id = $this->newSurveyReturnId(createdAt:new Carbon('2024-01-01'));
        $this->assertSame('2024-01-01 00:00:00', $this->experimentCreationDate($id));
    }

    #[Test]
    public function showUsersAssignedToSurveyMany(): void
    {
        // make sure that survey can have users assigned and display them
        $firstId = $this->dsl->newUserReturnId();
        $secondId = $this->dsl->newUserReturnId();
        $id = $this->newSurveyReturnId('Foo', usersId:[$firstId, $secondId]);
        $this->assertSame('2', $this->experimentUsers($id));
    }

    private function experimentTitle(int $id): string
    {
        $card = new Css('.card');
        $header = new Css('.card-header');
        return $this->dom("/Adm/Experiments/$id")->findString("//article/$card/$header/text()");
    }

    private function experimentUsers(int $id): string
    {
        $usersCountLine = new Css('.user-count');
        return $this->dom("/Adm/Experiments/$id")
            ->findString("//article//$usersCountLine/span/text()");
    }

    private function experimentCreationDate(int $id): string
    {
        $usersCountLine = new Css('.creation-date');
        return $this->dom("/Adm/Experiments/$id")
            ->findString("//article//$usersCountLine/span/text()");
    }

    #[Test]
    public function newSurveyTitleInputInNew(): void
    {
        // make sure that the new form screen has "tytuł" input
        $surveyInput = \trim($this->dom('/Adm/Experiments/Save')
            ->findString('//article//form//label/text()'));
        $this->assertSame('Tytuł', $surveyInput);
    }

    #[Test]
    public function newSurveyFormAction(): void
    {
        $surveyCreateUrl = $this->dom('/Adm/Experiments/Save')->findString('//article//form/@action');
        $this->assertStringEndsWith('/Adm/Experiments/Save', $surveyCreateUrl);
    }

    #[Test]
    public function createSurvey(): void
    {
        $response = $this->laravel->post('/Adm/Experiments/Save', ['title' => 'Foo']);
        $this->assertSame(302, $response->status());
        $this->assertSame('Foo', $this->lastSurvey()->title);
    }

    #[Test]
    public function createSurveyRequireTitle(): void
    {
        $response = $this->laravel->post('/Adm/Experiments/Save', ['title' => '']);
        $this->assertSame(302, $response->status());
        $this->assertFalse($this->lastSurveyExists());
    }

    #[Test]
    public function includeCsrfTokenInSurvey(): void
    {
        $this->setApplicationCsrfToken('abc123');
        $viewDom = $this->dom('/Adm/Experiments/Save');
        $this->assertSame('_token', $viewDom->findString("//article//form/input[@type='hidden']/@name"));
        $this->assertSame('abc123', $viewDom->findString("//article//form/input[@type='hidden']/@value"));
    }

    #[Test]
    public function showSurveyAfterCreatingIt(): void
    {
        $response = $this->laravel->post('/Adm/Experiments/Save', ['title' => 'work']);
        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith(
            "/Adm/Experiments/{$this->lastSurveyId()}",
            $response->headers->get('Location'));
    }

    private function lastSurveyId(): int
    {
        return $this->lastSurvey()->id;
    }

    private function lastSurvey(): Survey
    {
        /** @var Survey $survey */
        $survey = Survey::query()->firstOrFail();
        return $survey;
    }

    private function setApplicationCsrfToken(string $value): void
    {
        /** @var Session $session */
        $session = $this->laravel->app->get(Session::class);
        $session->put('_token', $value);
    }

    private function lastSurveyExists(): bool
    {
        return Survey::query()->exists();
    }

    private function experimentDom(int $surveyId): ViewDom
    {
        return $this->dom("/Adm/Experiments/$surveyId");
    }
}
