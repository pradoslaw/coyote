<?php
namespace Tests\Legacy\IntegrationNew\Seo\Schema\BreadcrumbList;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\IntegrationNew\Seo;

class JobApplicationTest extends TestCase
{
    use BaseFixture\Server\RelativeUri;
    use Seo\Schema\Fixture\JobOffer;

    public function test(): void
    {
        [$breadcrumbList, $id] = $this->jobOfferSchema('Orange offer');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            new ArrayStructure([
                'item' => $this->relativeUri("/Praca/$id-orange_offer"),
            ]));
    }
}
