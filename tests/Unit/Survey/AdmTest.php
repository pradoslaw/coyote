<?php
namespace Tests\Unit\Survey;

use Coyote\Models\Survey;
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
        return \array_map(\trim(...), $view->findStrings("//ul[@class='breadcrumb']/li/*/text()"));
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

    private function newSurvey(string $title): void
    {
        Survey::query()->create(['title' => $title]);
    }

    private function newSurveyReturnId(string $surveyTitle, array $usersId = null): int
    {
        /** @var Survey $survey */
        $survey = Survey::query()->create(['title' => $surveyTitle]);
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
    public function showUsersAssignedToSurveyMany(): void
    {
        $firstId = $this->dsl->newUserReturnId();
        $secondId = $this->dsl->newUserReturnId();
        $id = $this->newSurveyReturnId('Foo', usersId:[$firstId, $secondId]);
        $this->assertSame('2', $this->experimentUsers($id));
    }
}
