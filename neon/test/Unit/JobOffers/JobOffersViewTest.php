<?php
namespace Neon\Test\Unit\JobOffers;

use Neon\Domain;
use Neon\Domain\JobOffer;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Html\Body\JobOffers;
use Neon\View\ViewModel;
use PHPUnit\Framework\TestCase;

class JobOffersViewTest extends TestCase
{
    /**
     * @test
     */
    public function sectionTitle(): void
    {
        $view = $this->jobOffer(['sectionTitle' => 'Hear me roar']);
        $this->assertSame('Hear me roar',
            $view->find('#jobs', 'h2'));
    }

    /**
     * @test
     */
    public function jobOfferTitle(): void
    {
        $view = $this->jobOffer(['offerTitle' => 'The Lannisters send their regards']);
        $this->assertSame('The Lannisters send their regards',
            $view->find('#jobs', 'h3'));
    }

    /**
     * @test
     */
    public function jobOfferCompany(): void
    {
        $jobs = $this->jobOffer(['offerCompany' => 'Iron bank']);
        $this->assertSame('Iron bank',
            $jobs->find('#jobs', '#company'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesSummary(): void
    {
        $view = $this->jobOffer(['offerCities' => ['Braavos', 'Lorath', 'Norvos']]);
        $this->assertSame(
            '3 cities',
            $view->find('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesEmpty(): void
    {
        $view = $this->jobOffer(['offerCities' => []]);
        $this->assertSame(
            'Not provided',
            $view->find('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferRemoteWork(): void
    {
        $view = $this->jobOffer(['offerRemoteWork' => true]);
        $this->assertSame(
            'Remote work',
            $view->find('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesTitle(): void
    {
        $view = $this->jobOffer(['offerCities' => ['Braavos', 'Lorath', 'Norvos']]);
        $this->assertSame(
            'Braavos, Lorath, Norvos',
            $view->find('#jobs', '#cities', '@title'));
    }

    /**
     * @test
     */
    public function jobOfferTags(): void
    {
        $view = $this->jobOffer(['offerTags' => ['foo', 'bar']]);
        $this->assertSame(
            ['foo', 'bar'],
            $view->findMany('#jobs', '#tags', 'span'));
    }

    /**
     * @test
     */
    public function jobOfferImage(): void
    {
        $view = $this->jobOffer(['offerImage' => 'ice.and.fire.png']);
        $this->assertSame(
            'ice.and.fire.png',
            $view->find('#jobs', 'img', '@src'));
    }

    /**
     * @test
     */
    public function jobOffers(): void
    {
        $view = new ItemView(new JobOffers('', [
            new ViewModel\JobOffer(new JobOffer('foo', '', [], false, [], '')),
            new ViewModel\JobOffer(new JobOffer('bar', '', [], false, [], '')),
        ]));
        $this->assertSame(
            ['foo', 'bar'],
            $view->findMany('#jobs', 'h3'));
    }

    private function jobOffer(array $fields): ItemView
    {
        return new ItemView(new JobOffers(
            $fields['sectionTitle'] ?? '', [
            new ViewModel\JobOffer(new Domain\JobOffer(
                $fields['offerTitle'] ?? '',
                $fields['offerCompany'] ?? '',
                $fields['offerCities'] ?? [],
                $fields['offerRemoteWork'] ?? false,
                $fields['offerTags'] ?? [],
                $fields['offerImage'] ?? ''))]));
    }
}
