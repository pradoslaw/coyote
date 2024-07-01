<?php
namespace Tests\Unit\Legacy;

use Coyote\Flag;
use Coyote\Microblog;
use Coyote\Services\Flags;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture;

class FlagsTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use BaseFixture\Forum\Models;

    private Flags $flags;

    #[Before]
    public function initialize(): void
    {
        $this->flags = $this->laravel->app->make(Flags::class);
    }

    #[Before]
    public function removeFlags(): void
    {
        Flag::query()->delete();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function acceptEmptyPermissions(): void
    {
        $this->flags->get();
    }

    #[Test]
    public function shouldReturnReports(): void
    {
        $this->models->newPostReported(reportContent:'reported text');
        $flags = $this->flags->get();
        $this->assertSame(
            ['reported text'],
            $flags->pluck('text')->toArray());
    }

    #[Test]
    public function shouldFilterByRelation(): void
    {
        $this->models->newPostReported();
        $this->flags->fromModels([Microblog::class]);
        $this->assertEmpty($this->flags->get()->pluck('text')->toArray());
    }
}
