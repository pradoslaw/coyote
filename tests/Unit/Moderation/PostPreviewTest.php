<?php
namespace Tests\Unit\Moderation;

use Coyote\Domain\Administrator\View\PostMarkdown;
use Coyote\Domain\Administrator\View\PostPreview;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel\Application;

class PostPreviewTest extends TestCase
{
    use Application;

    /**
     * @test
     */
    public function empty(): void
    {
        $this->assertPreview('  ', '');
    }

    /**
     * @test
     */
    public function test(): void
    {
        $this->assertPreview('Short text.', 'Short text.');
    }

    /**
     * @test
     */
    public function ignoreQuote(): void
    {
        $this->assertPreview("> quote\n\ntext", 'text');
    }

    /**
     * @test
     */
    public function firstParagraph(): void
    {
        $this->assertPreview('<p>One</p> <p>Two</p>', 'One');
    }

    /**
     * @test
     */
    public function paragraphInList(): void
    {
        $this->assertPreviewNone("- one\n    two");
    }

    /**
     * @test
     */
    public function unicode(): void
    {
        $this->assertPreview('Łódź.', 'Łódź.');
    }

    /**
     * @test
     */
    public function acceptHtmlView(): void
    {
        $preview = new PostPreview('<video>Foo</video>Bar');
        $this->assertSame('', "$preview");
    }

    private function assertPreviewNone(string $postContent): void
    {
        $this->assertSame('', $this->newPost($postContent));
    }

    private function assertPreview(string $postContent, string $expectedPreview): void
    {
        $previewHtml = $this->newPost($postContent);
        $this->assertSame($expectedPreview, \trim($previewHtml));
    }

    private function newPost(string $postContent): string
    {
        $postMarkdown = new PostMarkdown($postContent);
        return $postMarkdown->previewHtml();
    }
}
