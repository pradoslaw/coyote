<?php
namespace Neon\Test\Unit\JobOffers;

use Neon\Domain;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Components\JobOffer\JobOffersHtml;
use Neon\View\Language\Polish;
use Neon\View\Theme;
use PHPUnit\Framework\TestCase;

class JobOffersViewLangPlTest extends TestCase
{
    /**
     * @test
     */
    public function jobOfferCitiesSummaryPluralNominative(): void
    {
        $view = $this->jobOffer(['offerCities' => ['Light', 'Dark']]);
        $this->assertSame(
            '2 miasta',
            $view->findText('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesSummaryPluralGenitive(): void
    {
        $view = $this->jobOffer(['offerCities' => [
            'Father', 'Mother', 'Maiden', 'Crone', 'Warrior', 'Smith', 'Stranger',
        ]]);
        $this->assertSame(
            '7 miast',
            $view->findText('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesEmpty(): void
    {
        $view = $this->jobOffer(['offerCities' => []]);
        $this->assertSame(
            'Nie podano',
            $view->findText('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferRemoteWork(): void
    {
        $view = $this->jobOffer(['offerRemoteWork' => true]);
        $this->assertSame(
            'Praca zdalna',
            $view->findText('#jobs', '#cities'));
    }

    private function jobOffer(array $fields): ItemView
    {
        return new ItemView(new JobOffersHtml('', [
            new \Neon\View\Components\JobOffer\JobOffer(new Polish(), new Domain\JobOffer(
                '',
                '',
                '',
                $fields['offerCities'] ?? [],
                $fields['offerRemoteWork'] ?? false,
                [],
                ''),
            )],
            new Theme(false)));
    }
}
