<?php
namespace Tests\Unit\BaseFixture\Forum;

use PHPUnit\Framework\Attributes\Before;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Server\Laravel\Transactional;

    var ModelsDriver $driver;

    #[Before]
    public function initializeModels(): void
    {
        $this->driver = new ModelsDriver();
    }
}
