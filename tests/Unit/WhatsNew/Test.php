<?php
namespace Tests\Unit\WhatsNew;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;
use Tests\Unit\BaseFixture\Constraint\ArrayStructure;
use Tests\Unit\BaseFixture\Constraint\TrimmedString;
use Tests\Unit\Seo;
use Tests\Unit\WhatsNew;

class Test extends TestCase
{
    use BaseFixture\ClearedCache;
    use BaseFixture\Server\RelativeUri;
    use WhatsNew\Fixture\Models;
    use WhatsNew\Fixture\NewsItems;

    public function test()
    {
        $id = $this->newWhatsNewItem('Valar morghulis.', '2005-04-02 21:37:13');
        $this->assertThat(
            $this->newsItem(),
            new ArrayStructure([
                'text' => new TrimmedString('Valar morghulis.'),
                'href' => $this->relativeUri("/Mikroblogi/View/$id"),
                'date' => new TrimmedString('— 02 kwi 05'),
            ]));
    }
}
