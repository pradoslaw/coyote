<?php

namespace Coyote\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;

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
            if ($this->limiter->tooManyAttempts($limit->key, $limit->maxAttempts)) {
                throw $this->buildException($request, $limit->key, $limit->maxAttempts, $limit->responseCallback);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        if ($this->isAttemptsLimited($request, $response)) {
            foreach ($limits as $limit) {
                $this->limiter->hit($limit->key, $limit->decayMinutes * 60);

                $response = $this->addHeaders(
                    $response,
                    $limit->maxAttempts,
                    $this->calculateRemainingAttempts($limit->key, $limit->maxAttempts)
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
}
