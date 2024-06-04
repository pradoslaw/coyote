<?php
namespace Coyote\Domain\Administrator\UserMaterial\Store;

class MaterialResult
{
    public function __construct(public array $materials, public int $total)
    {
    }
}
