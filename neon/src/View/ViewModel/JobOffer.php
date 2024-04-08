<?php
namespace Neon\View\ViewModel;

use Neon\Domain;

readonly class JobOffer
{
    public string $title;
    public ?string $company;
    public string $citiesSummary;
    public string $citiesTitle;
    public array $tags;
    public ?string $imageUrl;

    public function __construct(Domain\JobOffer $offer)
    {
        $this->title = $offer->title;
        $this->company = $offer->company;
        $this->tags = $offer->tags;
        $this->imageUrl = $offer->imageUrl;
        $this->citiesSummary = $this->citiesSummary($offer);
        $this->citiesTitle = \implode(', ', $offer->cities);
    }

    private function citiesSummary(Domain\JobOffer $offer): string
    {
        if ($offer->remoteWork) {
            return 'Remote work';
        }
        $count = \count($offer->cities);
        if ($count === 0) {
            return 'Not provided';
        }
        if ($count === 1) {
            return $offer->cities[0];
        }
        return "$count cities";
    }
}
