<?php
namespace Tests\Legacy\IntegrationNew\AdministratorUserMaterial\View;

use Coyote\Domain\Administrator\View\Html\InlineHtml;
use Coyote\Domain\Administrator\View\Html\PostMarkdown;
use Coyote\Domain\Administrator\View\Html\SubstringHtml;
use Coyote\Domain\Icon\Icons;
use Coyote\User;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel\Application;

class PostMarkdownTest extends TestCase
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
        $breakMark = $this->lineBreakMark();
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
        $this->assertPreview("> quote\n\ntext", $this->mark('contentMarkerQuote') . ' text');
    }

    /**
     * @test
     */
    public function markListItemsUnordered(): void
    {
        $listMark = $this->listMark();
        $this->assertPreview("- one\n- two", "{$listMark} one {$listMark} two");
    }

    /**
     * @test
     */
    public function markListItemsOrdered(): void
    {
        $listMark = $this->listMark();
        $this->assertPreview("1. one\n2. two", "{$listMark} one {$listMark} two");
    }

    /**
     * @test
     */
    public function markHeading(): void
    {
        $headingMark = $this->mark('contentMarkerHeading');
        $this->assertPreview('# heading', $headingMark . 'heading');
    }

    /**
     * @test
     */
    public function markHeadingBeforeParagraph(): void
    {
        $headingMark = $this->mark('contentMarkerHeading');
        $breakMark = $this->lineBreakMark();
        $this->assertPreview("# heading\nparagraph", $headingMark . 'heading ' . $breakMark . ' paragraph');
    }

    /**
     * @test
     */
    public function markHeadingBeforeParagraphHeading6(): void
    {
        $headingMark = $this->mark('contentMarkerHeading');
        $breakMark = $this->lineBreakMark();
        $this->assertPreview("###### heading\nparagraph", $headingMark . 'heading ' . $breakMark . ' paragraph');
    }

    /**
     * @test
     */
    public function markCodeBlock(): void
    {
        $this->assertPreview("```\ncode\n```", $this->mark('contentMarkerCode', 'code'));
    }

    /**
     * @test
     */
    public function markListItemLineBreak(): void
    {
        $listMark = $this->listMark();
        $breakMark = $this->lineBreakMark();
        $this->assertPreview("- one\n    two", "{$listMark} one {$breakMark} two");
    }

    /**
     * @test
     */
    public function markListItemNestedParagraph(): void
    {
        $listMark = $this->listMark();
        $breakMark = $this->lineBreakMark();
        $this->assertPreview("- one\n\n  two", "{$listMark}  one {$breakMark} two");
    }

    /**
     * @test
     */
    public function markVideo(): void
    {
        $preview = new InlineHtml('<video>Foo</video> Bar');
        $videoIcon = $this->mark('contentMarkerVideo', 'video');
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
        $breakMark = $this->lineBreakMark();
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
        return new SubstringHtml(new InlineHtml(new PostMarkdown($postContent)), 100);
    }

    private function mark(string $iconName, ?string $title = null): string
    {
        $icons = new Icons();
        return '<span class="badge badge-material-element">' .
            $icons->icon($iconName) .
            $this->markTitle($title) .
            '</span>';
    }

    private function markTitle(?string $title): string
    {
        if ($title) {
            return '<span class="ms-1">' . $title . '</span>';
        }
        return '';
    }

    private function lineBreakMark(): string
    {
        return '↵';
    }

    private function listMark(): string
    {
        return '•';
    }
}
