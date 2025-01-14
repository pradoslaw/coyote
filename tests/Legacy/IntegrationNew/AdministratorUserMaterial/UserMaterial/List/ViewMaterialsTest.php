<?php
namespace Tests\Legacy\IntegrationNew\AdministratorUserMaterial\UserMaterial\List;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\Store\Material;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialResult;
use Coyote\Domain\Administrator\UserMaterial\List\View\MarkdownRender;
use Coyote\Domain\Administrator\UserMaterial\List\View\MaterialItem;
use Coyote\Domain\Administrator\UserMaterial\List\View\MaterialList;
use Coyote\Domain\Administrator\UserMaterial\List\View\Time;
use Coyote\Domain\Html;
use Coyote\Domain\Icon\Icons;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server\Laravel\Application;

class ViewMaterialsTest extends TestCase
{
    use Application;

    /**
     * @test
     */
    public function typeComment(): void
    {
        $vo = $this->item($this->material(type:'comment'));
        $this->assertSame('komentarz', $vo->type);
    }

    /**
     * @test
     */
    public function typePost(): void
    {
        $vo = $this->item($this->material(type:'post'));
        $this->assertSame('post', $vo->type);
    }

    /**
     * @test
     */
    public function typeMicroblog(): void
    {
        $vo = $this->item($this->material(type:'microblog'));
        $this->assertSame('mikroblog', $vo->type);
    }

    /**
     * @test
     */
    public function contentHtml(): void
    {
        $vo = $this->item($this->material(markdown:'> welcome'));
        $this->assertHtml("<blockquote>\n<p>welcome</p>\n</blockquote>\n", $vo->content);
    }

    /**
     * @test
     */
    public function previewHtml(): void
    {
        $vo = $this->item($this->material(markdown:"> welcome\n\nparagraph"));
        $iconQuote = $this->icon('contentMarkerQuote');
        $mark = '<span class="badge badge-material-element">' . $iconQuote . '</span>';
        $this->assertHtml($mark . ' paragraph', $vo->preview);
    }

    /**
     * @test
     */
    public function previewHtmlTrimmed(): void
    {
        $vo = $this->item($this->material(markdown:\str_repeat('a', 150)));
        $this->assertHtml(\str_repeat('a', 100) . '...', $vo->preview);
    }

    /**
     * @test
     */
    public function previewHtmlTrimmedUnicode(): void
    {
        $vo = $this->item($this->material(markdown:$this->unicodeAtBoundary()));
        $this->assertHtml($this->unicodeAtBoundary(), $vo->preview);
    }

    /**
     * @test
     */
    public function createdAt(): void
    {
        $vo = $this->item($this->material(createdAt:new Carbon('2001-01-23 21:37:00')));
        $this->assertSame('2001-01-23 21:37:00', $vo->createdAt->format());
    }

    /**
     * @test
     */
    public function createdAgo(): void
    {
        $vo = $this->item($this->material(createdAt:new Carbon('2024-06-04 13:42:00')));
        $this->assertSame('100 lat 5 miesięcy temu', $vo->createdAt->ago());
    }

    /**
     * @test
     */
    public function deleted(): void
    {
        $vo = $this->item($this->material(deletedAt:new Carbon('2002-01-23 21:37:00')));
        $this->assertNotNull($vo->deletedAt);
    }

    /**
     * @test
     */
    public function existing(): void
    {
        $vo = $this->item($this->material(deletedAt:null));
        $this->assertNull($vo->deletedAt);
    }

    /**
     * @test
     */
    public function deletedAt(): void
    {
        $vo = $this->item($this->material(deletedAt:new Carbon('2002-01-23 21:37:00')));
        $this->assertSame('2002-01-23 21:37:00', $vo->deletedAt->format());
    }

    /**
     * @test
     */
    public function deletedAgo(): void
    {
        $vo = $this->item($this->material(deletedAt:new Carbon('2023-06-04 13:42:00')));
        $this->assertSame('101 lat 5 miesięcy temu', $vo->deletedAt->ago());
    }

    /**
     * @test
     */
    public function username(): void
    {
        $vo = $this->item($this->material(authorUsername:'Mark'));
        $this->assertSame('Mark', $vo->authorUsername);
    }

    /**
     * @test
     */
    public function total(): void
    {
        $vo = $this->materialVo(new MaterialResult([], 4));
        $this->assertSame(4, $vo->total());
    }

    private function unicodeAtBoundary(): string
    {
        return \str_repeat('a', 84) . '€';
    }

    private function item(Material $material): MaterialItem
    {
        $result = new MaterialResult([$material], 0);
        return $this->materialVo($result)->items()[0];
    }

    private function materialVo(MaterialResult $materials): MaterialList
    {
        return new MaterialList(
            app(MarkdownRender::class),
            new Time(new Carbon('2124-11-04 13:42:00')),
            $materials,
            new AvatarCdn());
    }

    private function material(
        ?string  $type = null,
        ?string  $markdown = null,
        Carbon  $createdAt = new Carbon(),
        ?Carbon $deletedAt = null,
        ?string  $authorUsername = null,
    ): Material
    {
        return new Material(
            0,
            $type ?? 'post',
            $createdAt,
            $deletedAt,
            null,
            null,
            $authorUsername ?? '',
            null,
            $markdown ?? '',
            false,
            false,
        );
    }

    private function assertHtml(string $expectedHtml, Html $content): void
    {
        $this->assertSame($expectedHtml, (string)$content);
    }

    private function icon(string $iconName): Html
    {
        $icons = new Icons();
        return $icons->icon($iconName);
    }
}
