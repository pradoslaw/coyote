<?php
namespace Tests\Acceptance;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
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
}
