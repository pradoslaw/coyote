<?php
namespace Neon\View\ViewModel;

use Neon\Domain;

readonly class JobOffer
{
    public string $title;
    public ?string $company;
    public string $city;
    public array $tags;
    public ?string $imageUrl;

    public function __construct(Domain\JobOffer $offer)
    {
        $this->title = $offer->title;
        $this->company = $offer->company;
        $this->tags = $offer->tags;
        $this->imageUrl = $offer->imageUrl;
        $this->city = $this->cities($offer);
    }

    private function cities(Domain\JobOffer $offer): string
    {
        $count = \count($offer->cities);
        if ($count === 1) {
            return $offer->cities[0];
        }
        return "$count cities";
    }
}
