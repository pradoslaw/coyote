<?php

namespace Coyote\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * Filter order/direction values an apply it to the query
     *
     * @param Builder $builder
     * @param $defaultColumn
     * @param $defaultDirection
     * @param array|null $accept
     * @param array $alias
     * @return mixed
     */
    public function scopeSortable(Builder $builder, $defaultColumn, $defaultDirection, array $accept = null, array $alias = [])
    {
        $direction = request('order', $defaultDirection) == 'asc' ? 'ASC' : 'DESC';
        $column = request('sort', $defaultColumn);

        if (request()->anyFilled(['sort', 'order'])) {
            if ($accept !== null && !in_array($column, $accept)) {
                $column = $defaultColumn;
            }


            // column can has a value. for example we want to filter by column topics.last_post_id
            // we don't want to pass such a long column name in query string. instead  we can set up an alias
            // like last that can be mapped as topics.last_post_id
            if (!empty($alias)) {
                foreach ($alias as $col => $val) {
                    if ($column === $col) {
                        $column = $val;
                    }
                }
            }
        }

        return $builder->orderBy($column, $direction);
    }
}
