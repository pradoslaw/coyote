<?php
namespace Tests\Unit\Seo\Schema\BreadcrumbList;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\BaseFixture\Constraint\ArrayKey;
use Tests\Unit\Seo;

class JobApplicationTest extends TestCase
{
    use BaseFixture\Server\RelativeUri;
    use Seo\Schema\Fixture\JobOffer;

    public function test(): void
    {
        [$breadcrumbList, $id] = $this->jobOfferSchema('Orange offer');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            $this->logicalAnd(
                new ArrayKey('@id', $this->relativeUri("/Praca/$id-orange_offer")),
                new ArrayKey('item', $this->relativeUri("/Praca/$id-orange_offer")),
            ));
    }
}
