<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Html;

class MaterialItem
{
    public function __construct(
        public string  $type,
        public string  $createdAt,
        public string  $createdAgo,
        public ?Date   $deletedAt,
        public string  $authorUsername,
        public ?string $authorImageUrl,
        public Html    $content,
        public Html    $preview,
        public bool    $reported,
        public ?string $adminUrl,
    )
    {
    }
}
