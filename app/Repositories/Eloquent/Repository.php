<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\CriteriaInterface;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use Illuminate\Database\Query\Expression;

abstract class Repository implements RepositoryInterface, CriteriaInterface
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var mixed
     */
    protected $model;

    /**
     * @var array
     */
    protected $criteria = [];

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * @param App $app
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->resetScope();
        $this->makeModel();
    }

    abstract protected function model();

    /**
     * Creates instance of model
     *
     * @return Model
     * @throws \Exception
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * @throws \Exception
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * @return $this
     */
    public function resetCriteria()
    {
        if (!empty($this->criteria)) {
            $this->criteria = [];
            $this->resetModel();
        }
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function skipCriteria($flag = true)
    {
        $this->skipCriteria = $flag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria)
    {
        $this->criteria[] = $criteria;
        return $this;
    }

    /**
     * @param callable|null $callable
     * @return $this
     */
    public function applyCriteria(callable $callable = null)
    {
        if ($this->skipCriteria === true) {
            if (!is_null($callable)) {
                return $callable();
            }

            return $this;
        }

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof Criteria) {
                $this->model = $criteria->apply($this->model, $this);
            }
        }

        if (!is_null($callable)) {
            $result = $callable();
            $this->resetModel();

            return $result;
        }

        return $this;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->applyCriteria(function () use ($columns) {
            return $this->model->get($columns);
        });
    }

    /**
     * @param  string $value
     * @param  string $key
     * @return array
     */
    public function pluck($value, $key = null)
    {
        $this->applyCriteria();
        $lists = $this->model->pluck($value, $key);

        if (!is_array($lists)) {
            $lists = $lists->all();
        }

        $this->resetModel();

        return $lists;
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->applyCriteria(function () use ($id, $columns) {
            return $this->model->find($id, $columns);
        });
    }

    /**
     * @param array $ids
     * @param array $columns
     * @return mixed
     */
    public function findMany(array $ids, $columns = ['*'])
    {
        return $this->applyCriteria(function () use ($ids, $columns) {
            return $this->model->findMany($ids, $columns);
        });
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrFail($id, $columns = ['*'])
    {
        return $this->applyCriteria(function () use ($id, $columns) {
            return $this->model->findOrFail($id, $columns);
        });
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function findOrNew($id, $columns = ['*'])
    {
        return $this->model->findOrNew($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->applyCriteria(function () use ($attribute, $value, $columns) {
            return $this->model->where($attribute, '=', $value)->first($columns);
        });
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = ['*'])
    {
        return $this->applyCriteria(function () use ($attribute, $value, $columns) {
            return $this->model->where($attribute, '=', $value)->get($columns);
        });
    }

    /**
     * @param array $where
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere($where, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model;

        foreach ($where as $field => $value) {
            $method = is_array($value) ? 'whereIn' : 'where';
            $model = $model->$method($field, $value);
        }

        $result = $model->get($columns);
        $this->resetModel();

        return $result;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        $paginator = $this->model->simplePaginate($perPage, $columns, $pageName, $page);
        $this->resetModel();

        return $paginator;
    }

    /**
     * @param null $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return mixed
     * @throws \Exception
     */
    public function paginateWithTopComments($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        $paginator = $this->model->paginate($perPage, $columns, $pageName, $page);
        $this->resetModel();

        return $paginator;
    }

    /**
     * @inheritdoc
     */
    public function last()
    {
        return $this->applyCriteria(function () {
            return $this->model->orderBy('id', 'DESC')->first();
        });
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Get a new raw query expression.
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function toSql(Builder $builder)
    {
        $sql = $builder->toSql();

        foreach ($builder->getBindings() as $binding) {
            $sql = preg_replace('/\?/', "'$binding'", $sql, 1);
        }

        return $sql;
    }
}
