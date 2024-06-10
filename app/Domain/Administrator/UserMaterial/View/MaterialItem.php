<?php
namespace Coyote\Domain\Administrator\UserMaterial\View;

use Coyote\Domain\Html;

class MaterialItem
{
    public function __construct(
        public string  $type,
        public string  $createdAt,
        public string  $createdAgo,
        public bool    $deleted,
        public string  $deletedAt,
        public string  $deletedAgo,
        public string  $authorUsername,
        public ?string $authorImageUrl,
        public Html    $content,
        public Html    $preview,
        public bool $reported,
    )
    {
    }
}
