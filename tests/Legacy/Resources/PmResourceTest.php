<?php

namespace Tests\Legacy\Resources;

use Coyote\Http\Resources\PmResource;
use Coyote\Pm;
use Tests\TestCase;

class PmResourceTest extends TestCase
{
    public function testTransformDataWithCounter()
    {
        $pm = factory(Pm::class)->state('id')->make();

        $data = (new PmResource($pm))->additional(['count' => 1])->toResponse(request())->getData(true);

        $this->assertEquals(1, $data['count']);
        $this->assertArrayHasKey('data', $data);
    }
}
