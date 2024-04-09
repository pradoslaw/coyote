<?php
namespace Neon\View\Components\JobOffer;

use Neon\Domain;
use Neon\View\Language\Language;

readonly class JobOffer
{
    public string $title;
    public string $url;
    public ?string $company;
    public string $citiesSummary;
    public string $citiesTitle;
    public array $tags;
    public ?string $imageUrl;

    public function __construct(private Language $lang, Domain\JobOffer $offer)
    {
        $this->title = $offer->title;
        $this->url = $offer->url;
        $this->company = $offer->company;
        $this->tags = $offer->tags;
        $this->imageUrl = $offer->imageUrl;
        $this->citiesSummary = $this->citiesSummary($offer);
        $this->citiesTitle = \implode(', ', $offer->cities);
    }

    private function citiesSummary(Domain\JobOffer $offer): string
    {
        if ($offer->remoteWork) {
            return $this->lang->t('Remote work');
        }
        $count = \count($offer->cities);
        if ($count === 0) {
            return $this->lang->t('Not provided');
        }
        if ($count === 1) {
            return $offer->cities[0];
        }
        return $count . ' ' . $this->lang->dec($count, 'cities');
    }
}
