<?php

namespace Coyote\Http\Forms\Wiki;

trait TreeListTrait
{
    /**
     * @return array
     */
    protected function getTreeList()
    {
        return $this->wiki->treeList();
    }
}
