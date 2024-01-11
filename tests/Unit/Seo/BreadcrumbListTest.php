<?php
namespace Tests\Unit\Seo;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class BreadcrumbListTest extends TestCase
{
    use BaseFixture\RelativeUri, Fixture\Schema;

    public function test(): void
    {
        $breadcrumbList = $this->schema('/Forum', 'BreadcrumbList');
        [$element] = $breadcrumbList['itemListElement'];
        $this->assertThat($element['name'], $this->identicalTo('Forum'));
        $this->assertThat($element['@id'], $this->relativeUri('/Forum'));
    }
}
