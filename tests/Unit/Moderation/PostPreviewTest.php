<?php
namespace Tests\Unit\Moderation;

use Coyote\Domain\Administrator\View\PostMarkdown;
use Coyote\Domain\Administrator\View\SubstringHtml;
use Coyote\Domain\StringHtml;
use Coyote\User;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\BaseFixture\Server\Laravel\Application;

class PostPreviewTest extends TestCase
{
    use Application;
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\ClearedCache;

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
    public function twoParagraphs(): void
    {
        $breakMark = $this->mark('fas fa-level-down-alt');
        $this->assertPreview('<p>One</p> <p>Two</p>', "One $breakMark Two");
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
    public function markQuote(): void
    {
        $this->assertPreview("> quote\n\ntext", $this->mark('fas fa-reply-all') . ' text');
    }

    /**
     * @test
     */
    public function markListItemsUnordered(): void
    {
        $listMark = $this->mark('fas fa-list-ol');
        $this->assertPreview("- one\n- two", "{$listMark} one {$listMark} two");
    }

    /**
     * @test
     */
    public function markListItemsOrdered(): void
    {
        $listMark = $this->mark('fas fa-list-ol');
        $this->assertPreview("1. one\n2. two", "{$listMark} one {$listMark} two");
    }

    /**
     * @test
     */
    public function markHeading(): void
    {
        $headingMark = $this->mark('fas fa-heading');
        $this->assertPreview('# heading', $headingMark . 'heading');
    }

    /**
     * @test
     */
    public function markHeadingBeforeParagraph(): void
    {
        $headingMark = $this->mark('fas fa-heading');
        $breakMark = $this->mark('fas fa-level-down-alt');
        $this->assertPreview("# heading\nparagraph", $headingMark . 'heading ' . $breakMark . ' paragraph');
    }

    /**
     * @test
     */
    public function markHeadingBeforeParagraphHeading6(): void
    {
        $headingMark = $this->mark('fas fa-heading');
        $breakMark = $this->mark('fas fa-level-down-alt');
        $this->assertPreview("###### heading\nparagraph", $headingMark . 'heading ' . $breakMark . ' paragraph');
    }

    /**
     * @test
     */
    public function markCodeBlock(): void
    {
        $this->assertPreview("```\ncode\n```", $this->mark('fas fa-code', 'code'));
    }

    /**
     * @test
     */
    public function markListItemLineBreak(): void
    {
        $listMark = $this->mark('fas fa-list-ol');
        $breakMark = $this->mark('fas fa-level-down-alt');
        $this->assertPreview("- one\n    two", "{$listMark} one {$breakMark} two");
    }

    /**
     * @test
     */
    public function markListItemNestedParagraph(): void
    {
        $listMark = $this->mark('fas fa-list-ol');
        $breakMark = $this->mark('fas fa-level-down-alt');
        $this->assertPreview("- one\n\n  two", "{$listMark}  one {$breakMark} two");
    }

    /**
     * @test
     */
    public function markVideo(): void
    {
        $preview = new SubstringHtml(new StringHtml('<video>Foo</video> Bar'), 50);
        $videoIcon = $this->mark('fas fa-film', 'video');
        $this->assertSame("$videoIcon Bar", "$preview");
    }

    /**
     * @test
     */
    public function retainBold(): void
    {
        $this->assertPreview('<b>Foo</b> Bar.', '<b>Foo</b> Bar.');
    }

    /**
     * @test
     */
    public function retainItalics(): void
    {
        $this->assertPreview('<i>Foo</i> Bar.', '<i>Foo</i> Bar.');
    }

    /**
     * @test
     */
    public function retainEmoticon(): void
    {
        $smile = '<img class="img-smile" src="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/svg/1f600.svg" alt="😀" title="Smiling Face">';
        $this->assertPreview('Hey! :smile: Hello', "Hey! $smile Hello");
    }

    /**
     * @test
     */
    public function linkNotClickable(): void
    {
        $this->assertPreview('[Foo](long-link) Bar', '<span class="fake-anchor">Foo</span> Bar');
    }

    /**
     * @test
     */
    public function mentionNotClickable(): void
    {
        $this->newUser('George');
        $this->assertPreview('@George', '<span class="fake-anchor fake-mention">@George</span>');
    }

    /**
     * @test
     */
    public function nestedInSpan(): void
    {
        $breakMark = $this->mark('fas fa-level-down-alt');
        $this->assertPreview('<span><br></span>', $breakMark);
    }

    function newUser(string $name): User
    {
        $admin = new User();
        $admin->name = $name;
        $admin->email = 'irrelevant';
        $admin->save();
        return $admin;
    }

    private function assertPreview(string $postContent, string $expectedPreview): void
    {
        $this->assertSame($expectedPreview, $this->newPost($postContent));
    }

    private function newPost(string $postContent): string
    {
        return new SubstringHtml(new PostMarkdown($postContent), 100);
    }

    private function mark(string $iconClass, string $title = null): string
    {
        if ($title === null) {
            $icon = '<i class="' . $iconClass . '"></i>';
        } else {
            $icon = '<i class="' . $iconClass . ' mr-1"></i>';
        }
        return '<span class="badge badge-material-element">' . $icon . $title . '</span>';
    }
}
