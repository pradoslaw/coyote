<?php
namespace Coyote\Domain\Administrator\UserMaterial;

use Carbon\Carbon;

readonly class Material
{
    public function __construct(
        public string $type,
        public Carbon $createdAt,
        public string $contentMarkdown,
    )
    {
    }
}
