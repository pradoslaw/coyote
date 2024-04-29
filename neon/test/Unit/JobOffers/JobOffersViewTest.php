<?php
namespace Neon\Test\Unit\JobOffers;

use Neon\Domain;
use Neon\Domain\JobOffer;
use Neon\Domain\Tag;
use Neon\Test\BaseFixture\ItemView;
use Neon\View\Components\JobOffer\JobOffersHtml;
use Neon\View\Language\English;
use Neon\View\Theme;
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
            $view->findText('#jobs', 'h2'));
    }

    /**
     * @test
     */
    public function jobOfferTitle(): void
    {
        $view = $this->jobOffer(['offerTitle' => 'The Lannisters send their regards']);
        $this->assertSame('The Lannisters send their regards',
            $view->findText('#jobs', 'h3', 'a'));
    }

    /**
     * @test
     */
    public function jobOfferLink(): void
    {
        $view = $this->jobOffer(['offerLink' => '/foo']);
        $this->assertSame(
            '/foo',
            $view->find('#jobs', 'h3', 'a', '@href'));
    }

    /**
     * @test
     */
    public function jobOfferCompany(): void
    {
        $jobs = $this->jobOffer(['offerCompany' => 'Iron bank']);
        $this->assertSame('Iron bank',
            $jobs->findText('#jobs', '#company'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesSummary(): void
    {
        $view = $this->jobOffer(['offerCities' => ['Braavos', 'Lorath', 'Norvos']]);
        $this->assertSame(
            '3 cities',
            $view->findText('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferCitiesEmpty(): void
    {
        $view = $this->jobOffer(['offerCities' => []]);
        $this->assertSame(
            'Not provided',
            $view->findText('#jobs', '#cities'));
    }

    /**
     * @test
     */
    public function jobOfferRemoteWork(): void
    {
        $view = $this->jobOffer(['offerRemoteWork' => true]);
        $this->assertSame(
            'Remote work',
            $view->findText('#jobs', '#cities'));
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
    public function jobOfferTagsTitle(): void
    {
        $view = $this->jobOffer(['offerTags' => [
            ['title' => 'foo'],
            ['title' => 'bar'],
        ]]);
        $this->assertSame(
            ['foo', 'bar'],
            $view->findTextMany('#jobs', '#tags', 'span'));
    }

    /**
     * @test
     */
    public function jobOfferTagsImage(): void
    {
        $view = $this->jobOffer(['offerTags' => [
            ['image' => 'foo'],
            ['image' => 'bar'],
        ]]);
        $this->assertSame(
            ['foo', 'bar'],
            $view->findMany('#jobs', '#tags', 'img', '@src'));
    }

    /**
     * @test
     */
    public function jobOfferTagNoImage(): void
    {
        $view = $this->jobOffer(['offerTags' => [['title' => 'foo']]]);
        $this->assertFalse($view->exists('#jobs', '#tags', 'img'));
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
        $titles = ['foo', 'bar'];
        $view = $this->jobOffersWithTitles($titles);
        $this->assertSame(
            ['foo', 'bar'],
            $view->findTextMany('#jobs', 'h3', 'a'));
    }

    private function jobOffer(array $fields): ItemView
    {
        return new ItemView(new JobOffersHtml(
            $fields['sectionTitle'] ?? '', [
            new \Neon\View\Components\JobOffer\JobOffer(
                new English(),
                new Domain\JobOffer(
                    $fields['offerTitle'] ?? '',
                    $fields['offerLink'] ?? '',
                    $fields['offerCompany'] ?? '',
                    $fields['offerCities'] ?? [],
                    $fields['offerRemoteWork'] ?? false,
                    $this->mapJobOfferTags($fields['offerTags'] ?? []),
                    $fields['offerImage'] ?? ''))],
            new Theme(false)));
    }

    private function mapJobOfferTags(array $tags): array
    {
        return \array_map(
            fn(array $tag) => new Tag($tag['title'] ?? '', $tag['image'] ?? null),
            $tags);
    }

    private function jobOffersWithTitles(array $titles): ItemView
    {
        return new ItemView(new JobOffersHtml('', \array_map(
            fn(string $title) => new \Neon\View\Components\JobOffer\JobOffer(
                new English(),
                new JobOffer($title, '', '', [], false, [], '')),
            $titles,
        ), new Theme(false)));
    }
}
