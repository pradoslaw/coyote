<?php
namespace Tests\Unit\Canonical\Topic;

use PHPUnit\Framework\TestCase;
use Tests\Unit\Canonical;

class PostEditTest extends TestCase
{
    use Canonical\Topic\Fixture\Models,
        Canonical\Topic\Fixture\PostEdit\Models,
        Canonical\Topic\Fixture\PostEdit\Assertion;

    public function test()
    {
        [$topicId, $postId, $author] = $this->newPostWithAuthor('pear-category');
        $this->assertCanonicalPostEdit($author, "/Forum/pear-category/Submit/$topicId/$postId");
    }

    /**
     * @test
     */
    public function otherForum()
    {
        // given
        $this->newForumSlug('apple-category');
        [$topicId, $postId, $author] = $this->newPostWithAuthor('pear-category');
        // when
        $this->assertRedirectPostEdit(
            $author,
            "/Forum/apple-category/Submit/$topicId/$postId",
            "/Forum/pear-category/Submit/$topicId/$postId",
            status:308);
    }
}
