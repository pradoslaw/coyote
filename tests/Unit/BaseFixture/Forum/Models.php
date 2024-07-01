<?php
namespace Tests\Unit\BaseFixture\Forum;

use PHPUnit\Framework\Attributes\Before;
use Tests\Unit\BaseFixture;

trait Models
{
    use BaseFixture\Server\Laravel\Transactional;

    var ModelsFactory $models;

    #[Before]
    public function initializeModels(): void
    {
        $this->models = new ModelsFactory();
    }
}
