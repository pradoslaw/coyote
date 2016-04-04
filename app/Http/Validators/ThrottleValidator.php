<?php

namespace Coyote;

/**
 * Anti flood validator
 *
 * @package Coyote
 */
class ThrottleValidator
{
    public function validateThrottle($attribute, $value, $parameters, $validator)
    {
        $request = request();
        $key = $request->ip();

        // kod uruchamiany poprzez codeception "nie widzi" metody browser()
        if (method_exists($request, 'browser')) {
            $key .= $request->browser();
        }

        $delay = 60;

        if (!empty($request->user())) {
            $key .= $request->user()->id;
            $delay = 15;
        }

        $key = 'flood:' . md5($key);

        $cache = app('cache');
        $flood = $cache->get($key, 0);

        if (!$flood || time() - $flood > $delay) {
            if (empty($validator->invalid())) {
                $cache->put($key, time(), $delay);
            }

            return true;
        }

        return false;
    }
}
