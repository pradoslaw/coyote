<?php
namespace Tests\Unit\Seo;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\Seo;
use Tests\Unit\Seo\Fixture\Constraint\ArrayKey;

class BreadcrumbListTest extends TestCase
{
    use Seo\BreadcrumbList\Fixture, BaseFixture\RelativeUri;

    /**
     * @test
     */
    public function forum(): void
    {
        $breadcrumbList = $this->breadcrumbsSchema();
        $this->assertThat(
            $breadcrumbList['itemListElement'][0],
            $this->logicalAnd(
                new ArrayKey('name', $this->identicalTo('Forum')),
                new ArrayKey('@id', $this->relativeUri('/Forum')),
            ));
    }

    /**
     * @test
     */
    public function category(): void
    {
        $breadcrumbList = $this->categorySchema('Orange category', 'orange-category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            $this->logicalAnd(
                new ArrayKey('name', $this->identicalTo('Orange category')),
                new ArrayKey('@id', $this->relativeUri('/Forum/orange-category')),
            ));
    }

    /**
     * @test
     */
    public function topic(): void
    {
        [$breadcrumbList, $topicId] = $this->topicSchema('Apple topic', 'apple-category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][2],
            $this->logicalAnd(
                new ArrayKey('name', $this->identicalTo('Apple topic')),
                new ArrayKey('@id', $this->relativeUri("/Forum/apple-category/$topicId-apple_topic")),
            ));
    }
}
