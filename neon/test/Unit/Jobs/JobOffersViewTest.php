<?php
namespace Neon\Test\Unit\Jobs;

use Neon\Domain\Offer;
use Neon\Test\BaseFixture\Selector\Selector;
use Neon\Test\BaseFixture\View\ViewDom;
use Neon\View\Html\JobOffers;
use Neon\View\HtmlView;
use PHPUnit\Framework\TestCase;

class JobOffersViewTest extends TestCase
{
    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $jobs = new JobOffers('title', []);
        $this->assertSame('title',
            $this->text($jobs, ['#jobs', 'h2']));
    }

    /**
     * @test
     */
    public function jobOfferTitle(): void
    {
        $jobs = new JobOffers('', [new Offer('job offer', '', [], [], '')]);
        $this->assertSame('job offer',
            $this->text($jobs, ['#jobs', 'h3']));
    }

    /**
     * @test
     */
    public function jobOfferCompany(): void
    {
        $jobs = new JobOffers('', [new Offer('', 'company', [], [], '')]);
        $this->assertSame('company',
            $this->text($jobs, ['#jobs', '#company']));
    }

    /**
     * @test
     */
    public function jobOfferCities(): void
    {
        $jobs = new JobOffers('', [new Offer('', '', ['city'], [], '')]);
        $this->assertSame('city',
            $this->text($jobs, ['#jobs', '#cities', 'span']));
    }

    /**
     * @test
     */
    public function jobOfferTags(): void
    {
        $jobs = new JobOffers('', [new Offer('', '', [], ['foo', 'bar'], '')]);
        $this->assertSame(
            ['foo', 'bar'],
            $this->texts($jobs, ['#jobs', '#tags', 'span']));
    }

    /**
     * @test
     */
    public function jobOfferImage(): void
    {
        $jobs = new JobOffers('', [new Offer('', '', [], [], 'image.png')]);
        $this->assertSame(
            'image.png',
            $this->text($jobs, ['#jobs', 'img', '@src']));
    }

    /**
     * @test
     */
    public function jobOffers(): void
    {
        $jobs = new JobOffers('', [
            new Offer('foo', '', [], [], ''),
            new Offer('bar', '', [], [], ''),
        ]);
        $this->assertSame(
            ['foo', 'bar'],
            $this->texts($jobs, ['#jobs', 'h3']));
    }

    private function texts(JobOffers $jobs, array $selectors): array
    {
        $selector = new Selector(...$selectors);
        return $this->viewDom($jobs)->findMany($selector->xPath());
    }

    private function text(JobOffers $jobs, array $selectors): string
    {
        $selector = new Selector(...$selectors);
        return $this->viewDom($jobs)->find($selector->xPath());
    }

    private function viewDom(JobOffers $jobs): ViewDom
    {
        $view = new HtmlView([], [$jobs]);
        return new ViewDom($view->html());
    }
}
