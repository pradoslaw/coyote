<?php
namespace Tests\Unit\Seo\Schema\BreadcrumbList;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\BaseFixture\Constraint\ArrayStructure;
use Tests\Unit\Seo;

/**
 * @see https://developers.google.com/search/docs/appearance/structured-data/breadcrumb
 */
class Test extends TestCase
{
    use Seo\Schema\BreadcrumbList\Fixture\Schema, BaseFixture\Server\RelativeUri;

    /**
     * @test
     */
    public function forum(): void
    {
        $breadcrumbList = $this->breadcrumbsSchema();
        $this->assertThat(
            $breadcrumbList['itemListElement'][0],
            new ArrayStructure([
                'name'     => 'Forum',
                '@id'      => $this->relativeUri('/Forum'),
                'item'     => $this->relativeUri('/Forum'),
                'position' => 1,
            ]));
    }

    /**
     * @test
     */
    public function category(): void
    {
        $breadcrumbList = $this->categorySchema('Orange category', 'orange-category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            new ArrayStructure([
                'name'     => 'Orange category',
                '@id'      => $this->relativeUri('/Forum/orange-category'),
                'item'     => $this->relativeUri('/Forum/orange-category'),
                'position' => 2,
            ]));
    }

    /**
     * @test
     */
    public function categoryParent(): void
    {
        $breadcrumbList = $this->categoryWithParentSchema('Banana category', 'banana_category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][1],
            new ArrayStructure([
                'name'     => 'Banana category',
                '@id'      => $this->relativeUri('/Forum/banana_category'),
                'item'     => $this->relativeUri('/Forum/banana_category'),
                'position' => 2,
            ]));
    }

    /**
     * @test
     */
    public function topic(): void
    {
        [$breadcrumbList, $topicId] = $this->topicSchema('Apple topic', 'apple-category');
        $this->assertThat(
            $breadcrumbList['itemListElement'][2],
            new ArrayStructure([
                'name'     => 'Apple topic',
                '@id'      => $this->relativeUri("/Forum/apple-category/$topicId-apple_topic"),
                'item'     => $this->relativeUri("/Forum/apple-category/$topicId-apple_topic"),
                'position' => 3,
            ]));
    }
}
