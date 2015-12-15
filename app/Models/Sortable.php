<?php

namespace Coyote;

trait Sortable
{
    /**
     * Filter order/direction values an apply it to the query
     *
     * @param $query
     * @param $sort
     * @param $order
     * @param array|null $accept
     * @param array $alias
     * @return mixed
     */
    public function scopeSortable($query, $sort, $order, array $accept = null, array $alias = [])
    {
        if (request()->has('sort') && request()->has('order')) {
            $order = request('order') == 'asc' ? 'ASC' : 'DESC';
            $sort = request('sort');

            if ($accept !== null) {
                if (!in_array($sort, $accept)) {
                    abort(500);
                }
            }

            // column can has a value. for example we want to filter by column topics.last_post_id
            // we don't want to pass such a long column name in query string. instead  we can set up an alias
            // like last that can be mapped as topics.last_post_id
            if ($alias) {
                foreach ($alias as $col => $val) {
                    if ($sort === $col) {
                        $sort = $val;
                    }
                }
            }
        }

        return $query->orderBy($sort, $order);
    }
}
