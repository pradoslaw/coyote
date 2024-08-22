<?php
namespace Tests\Unit\Survey;

use Neon\Test\BaseFixture\View\ViewDom;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Administration\Fixture\AdministratorPanel;

class AdmTest extends TestCase
{
    use AdministratorPanel;

    #[Before]
    public function administratorDashboard(): void
    {
        $this->userInAdministratorDashboard();
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
}
