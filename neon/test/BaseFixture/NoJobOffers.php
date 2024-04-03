<?php
namespace Neon\Test\BaseFixture;

use Neon\Persistence\JobOffers;

class NoJobOffers implements JobOffers
{
    public function fetchJobOffers(): array
    {
        return [];
    }
}
