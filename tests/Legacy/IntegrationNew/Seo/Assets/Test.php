<?php
namespace Tests\Legacy\IntegrationNew\Seo\Assets;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Constraint\ArrayStructure;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel;
use Tests\Legacy\IntegrationNew\Seo;

class Test extends TestCase
{
    use Laravel\Application;
    use Seo\Assets\Fixture\Fixture;

    public function test()
    {
        $id = $this->asset('file.txt');
        $this->assertThat($this->httpHeaders("/assets/$id/file.txt"), new ArrayStructure([
            'x-robots-tag' => $this->containsIdentical('noindex')
        ]));
    }

    public function httpHeaders(string $uri): array
    {
        return $this->laravel->get($uri)->headers->all();
    }
}
