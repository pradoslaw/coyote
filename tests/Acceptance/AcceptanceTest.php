<?php
namespace Tests\Acceptance;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\AcceptanceDsl\Dsl;

class AcceptanceTest extends TestCase
{
    private Dsl $dsl;

    #[Before]
    public function initializeDsl(): void
    {
        $this->dsl = new Dsl();
    }

    #[After]
    public function finalize(): void
    {
        if ($this->status()->isFailure() || $this->status()->isError()) {
            $this->dsl->includeDiagnosticArtifact($this);
        }
        $this->dsl->close();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function emptyTest(): void {}
}
