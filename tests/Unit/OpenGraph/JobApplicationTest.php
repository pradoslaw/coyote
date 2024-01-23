<?php
namespace Tests\Unit\OpenGraph;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;

class JobApplicationTest extends TestCase
{
    use BaseFixture\Server\RelativeUri;
    use BaseFixture\Server\Laravel\Transactional;
    use Seo\Fixture\JobOffer;
    use Fixture\OpenGraph;

    /**
     * @test
     */
    public function title()
    {
        $job = $this->newJobOffer('Banana offer');
        $this->assertThat(
            $this->ogProperty('og:title', uri:"/Praca/Application/$job->id"),
            $this->identicalTo("Aplikuj na stanowisko Banana offer :: 4programmers.net"));
    }
}
