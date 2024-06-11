<?php
namespace Tests\Unit\AdministratorUserMaterial\UserMaterial\List;

use Carbon\Carbon;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialRequest;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialStore;
use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Flag;
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
        [$material] = $this->fetch($this->request(type:'comment'));
        $this->assertSame('comment', $material->type);
    }

    /**
     * @test
     */
    public function createdAt(): void
    {
        $this->newPostCreatedAt('2185-01-23 21:37:00');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertDateTime('2185-01-23 21:37:00', $material->createdAt);
    }

    /**
     * @test
     */
    public function includeDeleted(): void
    {
        $this->newPostDeleted('deleted');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertSame('deleted', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function existingIsNotDeleted(): void
    {
        $this->newPost('content');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertNull($material->deletedAt);
    }

    /**
     * @test
     */
    public function deletedIsDeletedPost(): void
    {
        $this->newPostDeletedAt('content', '2185-01-23 21:37:00');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertDateTime('2185-01-23 21:37:00', $material->deletedAt);
    }

    /**
     * @test
     */
    public function deletedIsDeletedMicroblog(): void
    {
        $this->newMicroblogDeletedAt('content', '2186-01-23 21:37:00');
        [$material] = $this->fetch($this->request(type:'microblog'));
        $this->assertDateTime('2186-01-23 21:37:00', $material->deletedAt);
    }

    /**
     * @test
     */
    public function filterByDeleted(): void
    {
        $this->newPostDeleted('deleted');
        $this->newPost('existing');
        [$material] = $this->fetch($this->request(type:'post', deleted:true));
        $this->assertSame('deleted', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByNotDeleted(): void
    {
        $this->newPost('existing');
        $this->newPostDeleted('deleted');
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
        $this->newPostAuthor('Mark');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertSame('Mark', $material->authorUsername);
    }

    /**
     * @test
     */
    public function authorPhoto(): void
    {
        $this->newPostAuthorImage('image.jpg');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertSame('image.jpg', $material->authorImageUrl);
    }

    /**
     * @test
     */
    public function postNotReported(): void
    {
        $this->newPost('');
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertFalse($material->reported);
    }

    /**
     * @test
     */
    public function postReported(): void
    {
        $this->newPostReported();
        [$material] = $this->fetch($this->request(type:'post'));
        $this->assertTrue($material->reported);
    }

    /**
     * @test
     */
    public function filterByReported(): void
    {
        $this->newPostReported('reported');
        $this->newPost('regular');
        [$material] = $this->fetch($this->request(type:'post', reported:true));
        $this->assertSame('reported', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByNotReported(): void
    {
        $this->newPost('regular');
        $this->newPostReported('reported');
        [$material] = $this->fetch($this->request(type:'post', reported:false));
        $this->assertSame('regular', $material->contentMarkdown);
    }

    /**
     * @test
     */
    public function filterByAuthorId(): void
    {
        $first = $this->newPostWithAuthorId('important');
        $this->newPost('trivial');
        [$material] = $this->fetch($this->request(type:'post', authorId:$first));
        $this->assertSame('important', $material->contentMarkdown);
    }

    private function newPostWithAuthorId(string $content): int
    {
        $user = new User();
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();
        $this->storeThread(new Forum, new Topic, new Post(['text' => $content, 'user_id' => $user->id]));
        return $user->id;
    }

    private function newPostReported(string $content = ''): void
    {
        $post = new Post(['text' => $content]);
        $this->storeThread(new Forum, new Topic, $post);
        $user = $this->newUser();
        /** @var Flag $flag */
        $flag = Flag::query()->create([
            'type_id' => Flag\Type::query()->first()->id,
            'user_id' => $user->id,
            'url'     => '',
        ]);
        $flag->posts()->attach($post->id);
        $flag->save();
    }

    private function newPosts(array $contents): void
    {
        \array_walk($contents, $this->newPost(...));
    }

    private function newPost(string $content): void
    {
        $this->storeThread(new Forum, new Topic, new Post(['text' => $content]));
    }

    private function newPostDeleted(string $content): void
    {
        $post = new Post(['text' => $content]);
        $post->deleted_at = new Carbon();
        $this->storeThread(new Forum, new Topic, $post);
    }

    private function newPostDeletedAt(string $content, string $deletedAt): void
    {
        $post = new Post(['text' => $content]);
        $post->deleted_at = $this->deleteAtFieldValue($deletedAt);
        $this->storeThread(new Forum, new Topic, $post);
    }

    private function newPostAuthor(string $username): void
    {
        $user = new User();
        $user->name = $username;
        $user->email = 'irrelevant';
        $user->save();
        $this->storeThread(new Forum, new Topic, new Post(['user_id' => $user->id]));
    }

    private function newPostAuthorImage(string $image): void
    {
        $user = new User();
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->photo = $image;
        $user->save();
        $this->storeThread(new Forum, new Topic, new Post(['user_id' => $user->id]));
    }

    private function newMicroblogDeletedAt(string $content, string $deletedAt): void
    {
        $microblog = new Microblog();
        $microblog->user_id = $this->newUser()->id;
        $microblog->deleted_at = $this->deleteAtFieldValue($deletedAt);
        $microblog->text = $content;
        $microblog->save();
    }

    private function newPostCreatedAt(string $createdAt): void
    {
        $post = new Post();
        $post->created_at = new Carbon($createdAt, 'UTC');
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

    private function request(int    $page = 1,
                             int    $pageSize = 1,
                             string $type = null,
                             ?bool  $deleted = null,
                             ?bool  $reported = null,
                             ?int $authorId = null,
    ): MaterialRequest
    {
        return new MaterialRequest(
            $page,
            $pageSize,
            $type ?? 'post',
            $deleted,
            $reported,
            $authorId,
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
        $dateTime->setTimezone('UTC');
        $this->assertSame($expectedDateTime, $dateTime->toDateTimeString());
    }

    private function deleteAtFieldValue(string $deletedAt): string
    {
        $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $deletedAt, 'UTC');
        $carbon->setTimezone('Europe/Warsaw');
        return $carbon->toDateTimeString();
    }
}
