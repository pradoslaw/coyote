<?php
namespace Tests\Unit\AdministratorUserMaterial\UserMaterial;

use Carbon\Carbon;
use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialStore;
use Coyote\Forum;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class MaterialStoreTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Store;

    /**
     * @test
     */
    public function firstMaterial(): void
    {
        $this->newPost('welcome');
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
        $this->newMicroblog('microblog');
        $this->assertMicroblogContent(1, 1, ['microblog']);
    }

    /**
     * @test
     */
    public function typeComment(): void
    {
        $this->newComment('comment');
        $this->assertCommentContent(1, 1, ['comment']);
    }

    /**
     * @test
     */
    public function type(): void
    {
        $this->newComment('comment');
        [$material] = $this->fetch($this->request(1, 1, 'comment'));
        $this->assertSame('comment', $material->type);
    }

    /**
     * @test
     */
    public function createdAt(): void
    {
        $this->newPostCreatedAt('2185-01-23 21:37:00');
        [$material] = $this->fetch($this->request(1, 1, 'post'));
        $this->assertDateTime('2185-01-23 21:37:00', $material->createdAt);
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

    private function newPosts(array $contents): void
    {
        \array_walk($contents, $this->newPost(...));
    }

    private function newPost(string $content): void
    {
        $this->storeThread(new Forum, new Topic, new Post(['text' => $content]));
    }

    private function newPostCreatedAt(string $time): void
    {
        $post = new Post();
        $post->created_at = new Carbon($time, 'UTC');
        $this->storeThread(new Forum, new Topic, $post);
    }

    private function newMicroblog(string $content): void
    {
        $user = new User();
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();
        $microblog = new Microblog();
        $microblog->user_id = $user->id;
        $microblog->text = $content;
        $microblog->save();
    }

    private function newComment(string $content): void
    {
        $user = $this->newUser();
        $post = new Post(['text' => $content]);
        $this->storeThread(new Forum, new Topic, $post);
        $postComment = new Post\Comment();
        $postComment->post_id = $post->id;
        $postComment->user_id = $user->id;
        $postComment->text = $content;
        $postComment->save();
    }

    private function newUser(): User
    {
        $user = new User;
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();
        return $user;
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

    private function request(int $page, int $pageSize, string $type): MaterialRequest
    {
        return new MaterialRequest($page, $pageSize, $type);
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
        $materials = new MaterialStore();
        return $materials->fetch($request)->materials;
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
