<?php

/**
 * Generate a URL to a named route.
 * Overrides laravel's route function by setting $absolute to false.
 *
 * @param  string  $name
 * @param  array   $parameters
 * @param  bool    $absolute
 * @return string
 */
function route($name, $parameters = [], $absolute = false)
{
    return app('url')->route($name, $parameters, $absolute);
}
