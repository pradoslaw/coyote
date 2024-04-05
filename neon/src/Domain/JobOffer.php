<?php
namespace Neon\Domain;

readonly class JobOffer
{
    public function __construct(
        public string  $title,
        public ?string $company,
        public array   $cities,
        public array   $tags,
        public ?string $imageUrl,
    )
    {
    }
}
