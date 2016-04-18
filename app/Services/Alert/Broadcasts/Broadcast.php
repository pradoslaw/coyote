<?php

namespace Coyote\Services\Alert\Broadcasts;

/**
 * Class Broadcast
 */
abstract class Broadcast
{
    /**
     * @param array $data
     * @param $content
     * @return mixed
     */
    protected function parse(array $data, $content)
    {
        $template = [];

        foreach ($data as $key => $value) {
            $template['{' . $key . '}'] = $value;
        }
        return str_ireplace(array_keys($template), array_values($template), $content);
    }
}
