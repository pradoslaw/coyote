<?php

namespace Coyote\Services\Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany as EloquentHasMany;

class HasMany extends EloquentHasMany
{
    /**
     * Save models and remove old ones.
     *
     * @param  \Traversable|array  $models
     * @return \Traversable|array
     */
    public function push($models)
    {
        foreach ($models as $model) {
            $model->exists = false;

            unset($model->{$this->localKey});
        }

        $this->delete();
        $this->saveMany($models);

        return $models;
    }
}
