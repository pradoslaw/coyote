<?php
namespace Coyote\Domain\Administrator\UserMaterial\View;

use Coyote\Domain\Html;

class MaterialItem
{
    public function __construct(
        public string $type,
        public string $createdAt,
        public string $createdAgo,
        public Html   $content,
        public Html   $preview,
    )
    {
    }
}
