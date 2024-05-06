<?php
namespace Neon\Test\BaseFixture\Domain;

use Neon\Persistence\JobOffers;

class NoJobOffers implements JobOffers
{
    public function fetchJobOffers(): array
    {
        return [];
    }
}
