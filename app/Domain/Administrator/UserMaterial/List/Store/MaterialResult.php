<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

class MaterialResult
{
    /**
     * @param Material[] $materials
     * @param int $total
     */
    public function __construct(public array $materials, public int $total)
    {
    }
}
