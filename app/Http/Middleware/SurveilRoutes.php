<?php
namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SurveilRoutes
{
    public function handle(Request $request, Closure $next)
    {
        $requestRoute = Route::current();
        $controller = \get_class($requestRoute->controller);
        $this->updateOrInsert(
            route:$controller . '@' . $requestRoute->getActionMethod(),
            method:\implode('|', $requestRoute->methods));

        return $next($request);
    }

    private function updateOrInsert(string $route, string $method): void
    {
        $this->upsert(['route' => $route, 'method' => $method]);
    }

    private function upsert(array $bindings): void
    {
        $connection = DB::connection();
        $connection->update('UPDATE route_track SET count=count+1 WHERE route=:route AND method=:method;', $bindings);
        $connection->insert('
            INSERT INTO route_track (route, method, count)
               SELECT :route, :method, 1
               WHERE NOT EXISTS (SELECT 1 FROM route_track WHERE route=:route AND method=:method);',
            $bindings);
    }
}
