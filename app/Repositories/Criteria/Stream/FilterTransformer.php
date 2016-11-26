<?php

namespace Coyote\Repositories\Criteria\Stream;

use Coyote\Http\Forms\StreamFilterForm;
use Coyote\Repositories\Contracts\RepositoryInterface as Repository;
use Coyote\Repositories\Contracts\RepositoryInterface;
use Coyote\Repositories\Criteria\Criteria;

class FilterTransformer extends Criteria
{
    /**
     * @var array
     */
    protected $form;

    /**
     * @param StreamFilterForm $form
     */
    public function __construct(StreamFilterForm $form)
    {
        $this->form = array_only(
            $form->all(),
            ['ip', 'browser', 'fingerprint', 'actor_id', 'object_objectType', 'object_objectId']
        );
    }

    /**
     * @param \Illuminate\Database\Query\Builder $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        $builder = $model->orderBy('_id', 'DESC');

        foreach ($this->form as $key => $value) {
            $key = str_replace('_', '.', $key);

            if (!empty($value)) {
                if ('fingerprint' == $key || is_array($value)) {
                    $builder->whereIn($key, (array) $value);
                } elseif (is_string($value)) {
                    $builder->where($key, 'like', str_replace('*', '%', $value));
                } else {
                    $builder->where($key, $value);
                }
            }
        }

        return $builder;
    }
}
