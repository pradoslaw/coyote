<?php

namespace Coyote\Services\Elasticsearch;

class Raw
{
    /**
     * @param $query
     * @return mixed
     */
    public static function escape($query)
    {
        return str_replace(['/', '\:'], ['\/', ':'], preg_quote($query));
    }
}
