<?php
namespace Tests\Integration\View\Filter;

use Coyote\Domain\View\Filter\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /**
     * @test
     */
    public function test(): void
    {
        $this->assertFilter('', []);
    }

    /**
     * @test
     */
    public function filterByReported(): void
    {
        $this->assertFilter('is:reported', ['reported' => true]);
    }

    /**
     * @test
     */
    public function filterByNotReported(): void
    {
        $this->assertFilter('not:reported', ['reported' => false]);
    }

    /**
     * @test
     */
    public function filterByDeleted(): void
    {
        $this->assertFilter('is:deleted', ['deleted' => true]);
    }

    /**
     * @test
     */
    public function filterByAuthor(): void
    {
        $this->assertFilter('author:123', ['author' => 123]);
    }

    /**
     * @test
     */
    public function filterByAuthorZero(): void
    {
        $this->assertFilter('author:0', ['author' => 0]);
        $this->assertFilter('author:00', ['author' => 0]);
    }

    /**
     * @test
     */
    public function authorEmptyString(): void
    {
        $this->assertFilter('author:', []);
    }

    /**
     * @test
     */
    public function danglingWord(): void
    {
        $this->assertFilter('author', []);
    }

    /**
     * @test
     */
    public function nonInteger(): void
    {
        $this->assertFilter('author:abc', []);
    }

    /**
     * @test
     */
    public function filterByReportedAlsoAuthor()
    {
        $this->assertFilter('is:reported reporter:3 author:0', [
            'reported' => true,
            'reporter' => 3,
            'author'   => 0,
        ]);
    }

    /**
     * @test
     */
    public function ignoreSuperfluous()
    {
        $this->assertFilter('other:2', []);
        $this->assertFilter('other:abc', []);
        $this->assertFilter('is:other', []);
        $this->assertFilter('not:other', []);
    }

    /**
     * @test
     */
    public function filterByReporter()
    {
        $this->assertFilter('reporter:12', ['reporter' => 12]);
    }

    /**
     * @test
     */
    public function filterByReportOpen()
    {
        $this->assertFilter('is:open', ['open' => true]);
    }

    /**
     * @test
     */
    public function filterByTypePost()
    {
        $this->assertFilter('type:post', ['type' => 'post']);
    }

    /**
     * @test
     */
    public function filterByTypeComment()
    {
        $this->assertFilter('type:comment', ['type' => 'comment']);
    }

    /**
     * @test
     */
    public function filterByTypeMicroblog()
    {
        $this->assertFilter('type:microblog', ['type' => 'microblog']);
    }

    /**
     * @test
     */
    public function invalidType()
    {
        $this->assertFilter('type:other', []);
    }

    # by type: post, comment, microblog, (in the future all)
    # by category (in the future)

    private function assertFilter(string $format, array $expectedFilter): void
    {
        $filter = new Filter($format);
        $this->assertSame($expectedFilter, $filter->toArray());
    }
}
