<?php
namespace Neon\Test\Unit\JobOffers;

use Neon\Domain\Offer;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Html\Body\JobOffers;
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
    public function jobOfferCities(): void
    {
        $view = $this->jobOffer(['offerCities' => ['Braavos', 'Lorath', 'Norvos']]);
        $this->assertSame(
            ['Braavos', 'Lorath', 'Norvos'],
            $view->findMany('#jobs', '#cities', 'span'));
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
            new Offer('foo', '', [], [], ''),
            new Offer('bar', '', [], [], ''),
        ]));
        $this->assertSame(
            ['foo', 'bar'],
            $view->findMany('#jobs', 'h3'));
    }

    private function jobOffer(array $fields): ItemView
    {
        return new ItemView(new JobOffers(
            $fields['sectionTitle'] ?? '', [
            new Offer(
                $fields['offerTitle'] ?? '',
                $fields['offerCompany'] ?? '',
                $fields['offerCities'] ?? [],
                $fields['offerTags'] ?? [],
                $fields['offerImage'] ?? '')]));
    }
}
