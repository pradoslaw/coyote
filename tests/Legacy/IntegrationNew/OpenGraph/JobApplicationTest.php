<?php
namespace Tests\Legacy\IntegrationNew\OpenGraph;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\Seo;

class JobApplicationTest extends TestCase
{
    use BaseFixture\Server\RelativeUri;
    use BaseFixture\Server\Laravel\Transactional;
    use Seo\Schema\Fixture\JobOffer;
    use Fixture\OpenGraph;

    /**
     * @test
     */
    public function title()
    {
        $job = $this->newJobOffer('Banana offer');
        $this->assertThat(
            $this->metaProperty('og:title', uri:"/Praca/Application/$job->id"),
            $this->identicalTo("Aplikuj na stanowisko Banana offer :: 4programmers.net"));
    }
}
