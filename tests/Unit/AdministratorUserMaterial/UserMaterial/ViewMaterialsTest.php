<?php
namespace Tests\Unit\AdministratorUserMaterial\UserMaterial;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialResult;
use Coyote\Domain\Administrator\UserMaterial\View\MarkdownRender;
use Coyote\Domain\Administrator\UserMaterial\View\MaterialItem;
use Coyote\Domain\Administrator\UserMaterial\View\MaterialVo;
use Coyote\Domain\Administrator\UserMaterial\View\Time;
use Coyote\Domain\Html;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel\Application;

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
        $this->assertHtml('paragraph', $vo->preview);
    }

    /**
     * @test
     */
    public function previewHtmlTrimmed(): void
    {
        $vo = $this->item($this->material(markdown:\str_repeat('a', 150)));
        $this->assertHtml(\str_repeat('a', 85) . '...', $vo->preview);
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
        $this->assertSame('2001-01-23 21:37:00', $vo->createdAt);
    }

    /**
     * @test
     */
    public function createdAgo(): void
    {
        $vo = $this->item($this->material(createdAt:new Carbon('2024-06-04 13:42:00')));
        $this->assertSame('100 lat 5 miesięcy temu', $vo->createdAgo);
    }

    /**
     * @test
     */
    public function deleted(): void
    {
        $vo = $this->item($this->material(deletedAt:new Carbon('2002-01-23 21:37:00')));
        $this->assertTrue($vo->deleted);
    }

    /**
     * @test
     */
    public function existing(): void
    {
        $vo = $this->item($this->material(deletedAt:null));
        $this->assertFalse($vo->deleted);
    }

    /**
     * @test
     */
    public function deletedAt(): void
    {
        $vo = $this->item($this->material(deletedAt:new Carbon('2002-01-23 21:37:00')));
        $this->assertSame('2002-01-23 21:37:00', $vo->deletedAt);
    }

    /**
     * @test
     */
    public function deletedAgo(): void
    {
        $vo = $this->item($this->material(deletedAt:new Carbon('2023-06-04 13:42:00')));
        $this->assertSame('101 lat 5 miesięcy temu', $vo->deletedAgo);
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

    private function materialVo(MaterialResult $materials): MaterialVo
    {
        return new MaterialVo(
            app(MarkdownRender::class),
            new Time(new Carbon('2124-11-04 13:42:00')),
            $materials,
            new AvatarCdn());
    }

    private function material(
        string  $type = null,
        string  $markdown = null,
        Carbon  $createdAt = new Carbon(),
        ?Carbon $deletedAt = null,
        string  $authorUsername = null,
    ): Material
    {
        return new Material(
            $type ?? 'post',
            $createdAt,
                $deletedAt,
                $authorUsername ?? '',
                null,
            $markdown ?? '',
                false,
        );
    }

    private function assertHtml(string $expectedHtml, Html $content): void
    {
        $this->assertSame($expectedHtml, (string)$content);
    }
}
