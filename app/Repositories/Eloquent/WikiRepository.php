<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\WikiRepositoryInterface;
use Coyote\Wiki;

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
     * @param string $path
     * @return mixed
     */
    public function findByPath($path)
    {
        return $this
            ->model
            ->withTrashed() // @todo hmm, to chyba powinno byc dodane poprzez criteria a nie wpiasne "na stale"
            ->from('wiki_view')
            ->where('path', $path)
            ->first();
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function findByPathId($pathId)
    {
        return $this
            ->model
            ->from('wiki_view AS wiki')
            ->where('path_id', $pathId)
            ->first();
    }

    /**
     * Get children articles of given parent_id.
     *
     * @param int|null $parentId
     * @return mixed
     */
    public function children($parentId = null)
    {
        $this->applyCriteria();

        return $this
            ->model
            ->from($this->rawFunction('wiki_children', $parentId))
            ->get();
    }

    /**
     * @param int $pathId
     * @return mixed
     */
    public function parents($pathId)
    {
        $this->applyCriteria();

        return $this->model->from($this->rawFunction('wiki_parents', $pathId))->get();
    }

    /**
     * @return array
     */
    public function treeList()
    {
        $this->applyCriteria();

        $result = [];
        $data = $this->model->from($this->rawFunction('wiki_children'))->get(['path_id', 'title', 'depth']);

        foreach ($data as $row) {
            $result[$row['path_id']] = str_repeat('&nbsp;', $row['depth'] * 4) . $row['title'];
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getSubscribed($userId)
    {
        return $this
            ->app
            ->make(Wiki\Subscriber::class)
            ->select()
            ->join('wiki', 'wiki.id', '=', 'wiki_id')
            ->where('wiki_subscribers.user_id', $userId)
            ->orderBy('wiki_subscribers.id', 'DESC')
            ->paginate();
    }

    /**
     * @param $name
     * @param array ...$args
     * @return \Illuminate\Database\Query\Expression
     */
    private function rawFunction($name, ...$args)
    {
        foreach ($args as &$arg) {
            if ($arg === null) {
                $arg = 'NULL';
            }
        }

        return $this->raw(sprintf('%s(%s) AS "wiki"', $name, implode(',', $args)));
    }
}
