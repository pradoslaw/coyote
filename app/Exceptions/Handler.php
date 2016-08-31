<?php

namespace Coyote\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ForbiddenException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e)
    {
        // error handler to AJAX request
        if ($request->isXmlHttpRequest()) { // moze lepiej bedzie uzyc wantsJson()?
            $statusCode = 500;

            if ($this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();
            }

            if ($e instanceof HttpResponseException) {
                return parent::render($request, $e);
            }

            if ($e instanceof ValidationException && $e->getResponse()) {
                return response()->json($e->validator->errors(), $statusCode);
            }

            $response = [
                'error' => 'Przepraszamy, ale coś poszło nie tak. Prosimy o kontakt z administratorem.'
            ];

            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['message'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, $statusCode);
        }

        if ($e instanceof ForbiddenException) {
            return $this->renderForbiddenException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param ForbiddenException $e
     * @return \Illuminate\Http\Response
     */
    protected function renderForbiddenException(ForbiddenException $e)
    {
        return response()->view('errors.forbidden', $e->firewall->toArray(), 401);
    }

    /**
     * Get the html response content.
     *
     * @param  string  $content
     * @param  string  $css
     * @return string
     */
    protected function decorate($content, $css)
    {
        if (config('app.debug')) {
            return parent::decorate($content, $css);
        }

        // on production site, we MUST render "nice" error page
        return view('errors.500')->render();
    }
}
