<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermanentRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->filled('skills') || $request->filled('cities') || is_array($request->get('salary'))) {
            return $this->makeRedirection($request);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    private function makeRedirection(Request $request)
    {
        $all = $request->query->all();

        $all = $this->rename($all, 'skills', 'tag');
        $all = $this->rename($all, 'cities', 'city');

        if (isset($all['salary']['from'])) {
            $value = $all['salary']['from'];

            $all['salary'] = $value;
        }

        return redirect()->to($request->path() . '?' . http_build_query($all), 301);
    }

    /**
     * @param array $array
     * @param string $oldKey
     * @param string $newKey
     * @return array
     */
    private function rename(array $array, $oldKey, $newKey)
    {
        if (isset($array[$oldKey])) {
            $array[$newKey] = $array[$oldKey];
            unset($array[$oldKey]);
        }

        return $array;
    }
}
