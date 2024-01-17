<?php
namespace Tests\Legacy;

use Coyote\Http\Resources\TopicResource;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    /**
     * @before
     * @deprecated
     */
    public function resetLaravelStatic(): void
    {
        TopicResource::wrap('data');
    }
}
