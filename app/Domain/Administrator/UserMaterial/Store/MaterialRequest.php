<?php
namespace Coyote\Domain\Administrator\UserMaterial\Store;

class MaterialRequest
{
    public function __construct(
        public int    $page,
        public int    $pageSize,
        public string $type,
    )
    {
    }
}
