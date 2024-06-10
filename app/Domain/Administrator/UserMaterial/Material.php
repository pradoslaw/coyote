<?php
namespace Coyote\Domain\Administrator\UserMaterial;

use Carbon\Carbon;

readonly class Material
{
    public function __construct(
        public int  $id,
        public string  $type,
        public Carbon  $createdAt,
        public ?Carbon $deletedAt,
        public string  $authorUsername,
        public ?string $authorImageUrl,
        public string  $contentMarkdown,
        public bool $reported,
    )
    {
    }
}
