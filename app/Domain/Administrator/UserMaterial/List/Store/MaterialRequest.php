<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

class MaterialRequest
{
    public function __construct(
        public int    $page,
        public int    $pageSize,
        public string $type,
        public ?bool $deleted,
        public ?bool $reported,
    )
    {
    }
}
