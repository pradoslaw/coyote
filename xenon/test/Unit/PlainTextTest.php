<?php
namespace Xenon\Test\Unit;

use PHPUnit\Framework\TestCase;
use Xenon\Field;
use Xenon\TagField;
use Xenon\Xenon;

class PlainTextTest extends TestCase
{
    use Fixture;

    /**
     * @test
     */
    public function ssrField(): void
    {
        $xenon = new Xenon(
            [new Field('attack')],
            ['attack' => '<script>"attacked"</script>']);
        $this->assertHtml($xenon, '&lt;script&gt;"attacked"&lt;/script&gt;');
    }

    /**
     * @test
     */
    public function ssrTagField(): void
    {
        $xenon = new Xenon(
            [new TagField('p', 'attack')],
            ['attack' => '<script>"attacked"</script>']);
        $this->assertHtml($xenon, '<p>&lt;script&gt;"attacked"&lt;/script&gt;</p>');
    }
}
