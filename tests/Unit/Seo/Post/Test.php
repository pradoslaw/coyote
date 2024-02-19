<?php
namespace Tests\Unit\Seo\Post;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Topic;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class Test extends TestCase
{
    use BaseFixture\Forum\Store;

    /**
     * @test
     */
    function canonicalLink()
    {
        $id = $this->newPost('papaya-slug', 'Papaya topic');
        $this->assertRendered(
            $this->markdownLink("/Forum/$id"),
            $this->htmlLink("/Forum/papaya-slug/$id-papaya_topic"));
    }

    /**
     * @test
     */
    function pathMissingId()
    {
        $this->assertLinkUnchanged('/Forum');
    }

    /**
     * @test
     */
    function pathMissingIdTrailingSlash()
    {
        $this->assertLinkUnchanged('/Forum/');
    }

    /**
     * @test
     */
    function pathIdNonNumeric()
    {
        $this->assertLinkUnchanged('/Forum/123a');
    }

    /**
     * @test
     */
    function pathIdNonExistent()
    {
        $this->assertLinkUnchanged('/Forum/99999999');
    }

    /**
     * @test
     */
    function pathSegmentSuperfluous()
    {
        $id = $this->newPost();
        $this->assertLinkUnchanged("/Forum/Forum/$id");
    }

    /**
     * @test
     */
    function pathSegmentMissing()
    {
        $id = $this->newPost();
        $this->assertLinkUnchanged("/$id");
    }

    /**
     * @test
     */
    function pathSegmentIncorrectBase()
    {
        $id = $this->newPost();
        $this->assertLinkUnchanged("/other/$id");
    }

    /**
     * @test
     */
    function pathMissingLeadingSlash()
    {
        $id = $this->newPost();
        $this->assertLinkUnchanged("Forum/$id");
    }

    /**
     * @test
     */
    function malformedUrl()
    {
        $this->assertRendered(
            $this->markdownLink(':'),
            $this->htmlLink('%3A'));
    }

    /**
     * @test
     */
    function acceptsQueryString()
    {
        $id = $this->newPost('guava-slug', 'Guava topic');
        $this->assertRendered(
            $this->markdownLink("/Forum/$id?foo=bar"),
            $this->htmlLink("/Forum/guava-slug/$id-guava_topic"));
    }

    private function assertLinkUnchanged(string $uri): void
    {
        $this->assertRendered(
            $this->markdownLink($uri),
            $this->htmlLink($uri));
    }

    private function assertRendered(string $markdown, string $html): void
    {
        $this->assertSame(
            $html,
            \rTrim($this->rendered($markdown), "\n"));
    }

    private function rendered(string $markdown): string
    {
        return (new Post(['text' => $markdown]))->html;
    }

    private function newPost(string $forumSlug = null, string $topicTitle = null): int
    {
        $topic = $this->storeThread(
            new Forum(['slug' => $forumSlug]),
            new Topic(['title' => $topicTitle]));
        return $topic->first_post_id;
    }

    private function markdownLink(string $uri): string
    {
        return "[title]($uri)";
    }

    private function htmlLink(string $uri): string
    {
        return '<p><a href="' . $uri . '" rel="ugc,nofollow">title</a></p>';
    }
}
