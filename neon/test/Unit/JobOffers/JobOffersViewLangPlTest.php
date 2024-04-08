<?php
namespace Neon\Test\Unit\JobOffers;

use Neon\Domain;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Html\Body\JobOffers;
use Neon\View\Language\Polish;
use Neon\View\ViewModel;
use PHPUnit\Framework\TestCase;

class JobOffersViewLangPlTest extends TestCase
{
    /**
     * @test
     */
    public function jobOfferCitiesSummary(): void
    {
        $view = $this->jobOffer(['offerCities' => ['Braavos', 'Lorath', 'Norvos']]);
        $this->assertSame(
            '3 miast',
            $view->find('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesEmpty(): void
    {
        $view = $this->jobOffer(['offerCities' => []]);
        $this->assertSame(
            'Nie podano',
            $view->find('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferRemoteWork(): void
    {
        $view = $this->jobOffer(['offerRemoteWork' => true]);
        $this->assertSame(
            'Praca zdalna',
            $view->find('#jobs', '#cities'));
    }

    private function jobOffer(array $fields): ItemView
    {
        return new ItemView(new JobOffers('', [
            new ViewModel\JobOffer(new Polish(), new Domain\JobOffer(
                '',
                '',
                $fields['offerCities'] ?? [],
                $fields['offerRemoteWork'] ?? false,
                [],
                ''),
            )]));
    }
}
