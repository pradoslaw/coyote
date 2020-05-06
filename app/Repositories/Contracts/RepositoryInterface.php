<?php

namespace Coyote\Repositories\Contracts;

use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Creates instance of model
     *
     * @return Model
     * @throws \Exception
     */
    public function makeModel();

    /**
     * @throws \Exception
     */
    public function resetModel();

    /**
     * @return $this
     */
    public function resetCriteria();

    /**
     * @param bool $flag
     * @return $this
     */
    public function skipCriteria($flag = true);

    /**
     * @return mixed
     */
    public function getCriteria();

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria);

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * @return $this
     */
    public function applyCriteria();

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public function pluck($value, $key = null);

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = 'id');

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * @param array $ids
     * @param array $columns
     * @return mixed
     */
    public function findMany(array $ids, $columns = ['*']);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrFail($id, $columns = ['*']);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrNew($id, $columns = ['*']);

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*']);

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = ['*']);

    /**
     * @param array $where
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere($where, $columns = ['*']);

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * @param int|null $perPage
     * @param array $columns
     * @param string $pageName
     * @param int|null $page
     * @return mixed
     * @throws \Exception
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Get last element.
     *
     * @return mixed
     */
    public function last();

    /**
     * Get a new raw query expression.
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value);
}
