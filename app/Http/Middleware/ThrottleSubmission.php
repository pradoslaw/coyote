<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSubmission extends ThrottleRequests
{
    /**
     * @param $request
     * @param Closure $next
     * @param array $limits
     * @return Response
     */
    protected function handleRequest($request, Closure $next, array $limits)
    {
        foreach ($limits as $limit) {
            // check two things:
            // 1. has the user reached the request limit?
            // 2. whether the optional parameters are filled in (that can suggest that the user is editing existing an resource)
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts) && !$this->isOptionalFilled($request->route())) {
                throw $this->buildException($request, $limit->key, $limit->maxAttempts, $limit->responseCallback);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        if ($this->isAttemptsLimited($request, $response)) {
            foreach ($limits as $limit) {
                $this->limiter->hit($limit->key, $limit->decaySeconds);

                $response = $this->addHeaders(
                    $response,
                    $limit->maxAttempts,
                    $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts),
                );
            }
        }

        return $response;
    }

    protected function buildException($request, $key, $maxAttempts, $responseCallback = null)
    {
        $exception = parent::buildException($request, $key, $maxAttempts, $responseCallback);

        if ($exception instanceof ThrottleRequestsException) {
            return new ThrottleRequestsException('Zbyt wiele prób. Spróbuj za chwilę.');
        }

        return $exception;
    }

    protected function isAttemptsLimited(Request $request, Response $response): bool
    {
        return $response->getStatusCode() === Response::HTTP_CREATED && $request->user()->reputation < 100;
    }

    private function isOptionalFilled(Route $route): bool
    {
        $routeValues = collect($route->parameters())
            ->map(fn($parameter) => !($parameter instanceof Model && !$parameter->exists) && !empty($parameter))
            ->filter()
            ->keys()
            ->toArray();

        $routeKeys = $route->compiled->getVariables();

        return $routeKeys === $routeValues;
    }
}
