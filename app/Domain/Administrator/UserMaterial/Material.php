<?php
namespace Coyote\Domain\Administrator\UserMaterial;

use Carbon\Carbon;

readonly class Material
{
    public function __construct(
        public string  $type,
        public Carbon  $createdAt,
        public ?Carbon $deletedAt,
        public string  $authorUsername,
        public ?string $authorImageUrl,
        public string  $contentMarkdown,
    )
    {
    }
}
