<?php
namespace Xenon\Test\Unit;

use PHPUnit\Framework\TestCase;
use Xenon\TagField;
use Xenon\Xenon;

class PlainTextTest extends TestCase
{
    use Fixture;

    private Xenon $xenon;

    /**
     * @before
     */
    public function xssAttack(): void
    {
        $this->xenon = new Xenon([
            new TagField('p', 'attack')],
            ['attack' => '<script>"attacked"</script>']);
    }

    /**
     * @test
     */
    public function ssr(): void
    {
        $this->assertHtml($this->xenon, '<p>&lt;script&gt;"attacked"&lt;/script&gt;</p>');
    }
}
