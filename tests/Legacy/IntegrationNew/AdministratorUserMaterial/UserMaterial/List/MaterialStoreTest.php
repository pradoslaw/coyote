<?php
namespace Tests\Legacy\IntegrationNew\AdministratorUserMaterial\UserMaterial\List;

use Carbon\Carbon;
use Coyote\Domain\Administrator\UserMaterial\List\Store\Material;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialStore;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

class MaterialStoreTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Models;

    /**
     * @test
     */
    public function firstMaterial(): void
    {
        $this->driver->newPost('welcome');
        $this->assertPostContent(1, 1, ['welcome']);
    }

    /**
     * @test
     */
    public function secondMaterial(): void
    {
        $this->newPosts(['second', 'first']);
        $this->assertPostContent(2, 1, ['second']);
    }

    /**
     * @test
     */
    public function materialsOnNextPage(): void
    {
        $this->newPosts(['fifth', 'fourth', 'third', 'second', 'first']);
        $this->assertPostContent(2, 2, ['third', 'fourth']);
    }

    /**
     * @test
     */
    public function typeMicroblog(): void
    {
        $this->driver->newMicroblog('microblog');
        $this->assertMicroblogContent(1, 1, ['microblog']);
    }

    /**
     * @test
     */
    public function typeComment(): void
    {
        $this->driver->newComment('comment');
        $this->assertCommentContent(1, 1, ['comment']);
    }

    /**
     * @test
     */
    public function type(): void
    {
        $this->driver->newComment('comment');
        [$material] = $this->fetch($this->request(type:'comment'));
        $this->assertSame('comment', $material->type);
    }

    /**
     * @test
     */
    public function createdAt(): void
    {
        $this->driver->newPostCreatedAt('2185-01-23 21:37:00');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertDateTime('2185-01-23 22:37:00', $material->createdAt);
    }

    /**
     * @test
     */
    public function includeDeleted(): void
    {
        $this->driver->newPostDeleted('deleted');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertSame('deleted', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function existingIsNotDeleted(): void
    {
        $this->driver->newPost('content');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertNull($material->deletedAt);
    }

    /**
     * @test
     */
    public function deletedIsDeletedPost(): void
    {
        $this->driver->newPostDeletedAt('content', '2185-01-23 21:37:00');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertDateTime('2185-01-23 21:37:00', $material->deletedAt);
    }

    /**
     * @test
     */
    public function deletedIsDeletedMicroblog(): void
    {
        $this->driver->newMicroblogDeletedAt('content', '2186-01-23 21:37:00');
        [$material] = $this->fetch($this->request(type:'microblog'));
        $this->assertDateTime('2186-01-23 21:37:00', $material->deletedAt);
    }

    /**
     * @test
     */
    public function filterByDeleted(): void
    {
        $this->driver->newPostDeleted('deleted');
        $this->driver->newPost('existing');
        [$material] = $this->fetch($this->request(type:'post', deleted:true));
        $this->assertSame('deleted', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByNotDeleted(): void
    {
        $this->driver->newPost('existing');
        $this->driver->newPostDeleted('deleted');
        [$material] = $this->fetch($this->request(type:'post', deleted:false));
        $this->assertSame('existing', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function total(): void
    {
        $total = $this->fetchTotal($this->request(1, 1, 'comment'));
        // TODO In order to be able to test it, we must somehow control amount 
        // of material in database. To be continued.
        $this->assertSame('integer', \getType($total));
    }

    /**
     * @test
     */
    public function authorUsername(): void
    {
        $this->driver->newPostAuthor('Mark');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertSame('Mark', $material->authorUsername);
    }

    /**
     * @test
     */
    public function authorPhoto(): void
    {
        $this->driver->newPostAuthorPhoto('image.jpg');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertSame('image.jpg', $material->authorImageUrl);
    }

    /**
     * @test
     */
    public function postNotReported(): void
    {
        $this->driver->newPost('');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertFalse($material->reported);
    }

    /**
     * @test
     */
    public function postReported(): void
    {
        $this->driver->newPostReported('');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertTrue($material->reported);
    }

    /**
     * @test
     */
    public function postReportedOpen(): void
    {
        $this->driver->newPostReported();
        [$material] = $this->fetch($this->request());
        $this->assertTrue($material->reportOpen);
    }

    /**
     * @test
     */
    public function postReportedClosed(): void
    {
        $this->driver->newPostReportedClosed('');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertFalse($material->reportOpen);
    }

    /**
     * @test
     */
    public function filterByReported(): void
    {
        $this->driver->newPostReported('reported');
        $this->driver->newPost('regular');
        [$material] = $this->fetch($this->request(type:'post', reported:true));
        $this->assertSame('reported', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByNotReported(): void
    {
        $this->driver->newPost('regular');
        $this->driver->newPostReported('reported');
        [$material] = $this->fetch($this->request(type:'post', reported:false));
        $this->assertSame('regular', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByAuthorId(): void
    {
        $userId = $this->driver->newPostReturnAuthorId('important');
        $this->driver->newPost('trivial');
        [$material] = $this->fetch($this->request(type:'post', authorId:$userId));
        $this->assertSame('important', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByReportClosed(): void
    {
        $this->driver->newPostReportedClosed('reported-closed');
        $this->driver->newPostReported('reported');
        [$material] = $this->fetch($this->request(type:'post', reportOpen:false));
        $this->assertSame('reported-closed', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByReportOpen(): void
    {
        $this->driver->newPostReported('reported-open');
        $this->driver->newPostReportedClosed('reported-closed');
        [$material] = $this->fetch($this->request(type:'post', reportOpen:true));
        $this->assertSame('reported-open', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function microblogFilterByReportOpen(): void
    {
        $this->driver->newMicroblogReported('reported-open');
        $this->driver->newMicroblogReportedClosed('reported-closed');
        [$material] = $this->fetch($this->request(type:'microblog', reportOpen:true));
        $this->assertSame('reported-open', $material->contentMarkdown);
    }

    private function newPosts(array $contents): void
    {
        \array_walk($contents, $this->driver->newPost(...));
    }

    private function assertMicroblogContent(int $page, int $pageSize, array $expectedText): void
    {
        $this->assertMaterialContent(
            $expectedText,
            $this->request($page, $pageSize, 'microblog'));
    }

    private function assertCommentContent(int $page, int $pageSize, array $expectedText): void
    {
        $this->assertMaterialContent(
            $expectedText,
            $this->request($page, $pageSize, 'comment'));
    }

    private function assertPostContent(int $page, int $pageSize, array $expectedText): void
    {
        $this->assertMaterialContent(
            $expectedText,
            $this->request($page, $pageSize, 'post'));
    }

    private function request(int    $page = 1,
                             int    $pageSize = 1,
                             ?string $type = null,
                             ?bool  $deleted = null,
                             ?bool  $reported = null,
                             ?int   $authorId = null,
                             ?bool  $reportOpen = null,
    ): MaterialRequest
    {
        return new MaterialRequest(
            $page,
            $pageSize,
            $type ?? 'post',
            $deleted,
            $reported,
            $authorId,
            $reportOpen,
        );
    }

    private function assertMaterialContent(array $expectedTexts, MaterialRequest $request): void
    {
        $this->assertSame(
            $expectedTexts,
            $this->materialTexts($this->fetch($request)));
    }

    private function materialTexts(array $materials): array
    {
        return \array_map(fn(Material $m) => $m->contentMarkdown, $materials);
    }

    /**
     * @return Material[]
     */
    private function fetch(MaterialRequest $request): array
    {
        $store = new MaterialStore();
        $materials = $store->fetch($request)->materials;
        $this->assertNotEmpty($materials, 'Failed to assert that material was fetched.');
        return $materials;
    }

    private function fetchTotal(MaterialRequest $request): int
    {
        $materials = new MaterialStore();
        return $materials->fetch($request)->total;
    }

    private function assertDateTime(string $expectedDateTime, Carbon $dateTime): void
    {
        $this->assertSame($expectedDateTime, $dateTime->toDateTimeString());
    }
}
