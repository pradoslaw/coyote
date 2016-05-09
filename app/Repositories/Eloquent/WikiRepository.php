<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\WikiRepositoryInterface;

class WikiRepository extends Repository implements WikiRepositoryInterface
{
    /**
     * @return \Coyote\Wiki
     */
    public function model()
    {
        return 'Coyote\Wiki';
    }

    /**
     * Get children articles of given parent_id.
     *
     * @param int|null $parentId
     * @param int $depth
     * @return mixed
     */
    public function children($parentId = null, $depth = 10)
    {
        $this->applyCriteria();

        return $this
            ->model
            ->from($this->sqlFunction('wiki_children', $parentId, $depth))
            ->get();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function parents($id)
    {
        $this->applyCriteria();

        return $this->model->from($this->sqlFunction('wiki_parents', $id))->get();
    }

    /**
     * @param $name
     * @param array ...$args
     * @return \Illuminate\Database\Query\Expression
     */
    private function sqlFunction($name, ...$args)
    {
        foreach ($args as &$arg) {
            if ($arg === null) {
                $arg = 'NULL';
            }
        }

        return $this->raw(sprintf('%s(%s)', $name, implode(',', $args)));
    }
}
