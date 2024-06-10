<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

use Coyote\Domain\Administrator\UserMaterial\Material;

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
