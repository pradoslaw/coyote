<?php

namespace Tests\Unit\Resources\Elasticsearch;

use Coyote\Http\Resources\Elasticsearch\JobResource;
use Coyote\Job;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class JobResourceTest extends TestCase
{
    use DatabaseTransactions;

    public function testMapModelWithFirm()
    {
        $job = factory(Job::class)->state('firm')->create();

        $this->assertNotNull($job->firm_id);

        JobResource::withoutWrapping();
        $result = JobResource::make($job)->toResponse(request())->getData(true);

        $this->assertNotEmpty($result['firm']);

        $this->assertEmpty($result['firm']['logo']);
        $this->assertEquals($job->firm->name, $result['firm']['name']);
    }

    public function testMapModelWithoutFirm()
    {
        $job = factory(Job::class)->create();

        $this->assertNull($job->firm_id);

        JobResource::withoutWrapping();
        $result = JobResource::make($job)->toResponse(request())->getData(true);

        $this->assertArrayNotHasKey('firm', $result);
    }
}
