<?php

namespace Coyote\Exceptions;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        ForbiddenException::class,
        CommandNotFoundException::class,
        PaymentFailedException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
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
        if ($this->shouldReport($e)) {
            // log input data and url for further analyse
            logger()->error('+', ['url' => request()->url(), 'input' => request()->all(), 'ip' => request()->ip()]);

            if (app()->environment('production')) {
                // send report to sentry
                app('sentry')->captureException($e);
            }
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
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
            } elseif ($e instanceof ValidationException) {
//            if ($e instanceof ValidationException && $e->getResponse()) {
                return response()->json($e->validator->errors(), $statusCode);
            } elseif ($e instanceof TokenMismatchException) {
                return $this->renderTokenMismatchException($request, $e);
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
        } elseif ($e instanceof TokenMismatchException) {
            return $this->renderTokenMismatchException($request, $e);
        } elseif (($e instanceof HttpException && $e->getStatusCode() === 404) || $e instanceof ModelNotFoundException) {
            return $this->renderHttpErrorException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param Request $request
     * @param $e
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function renderTokenMismatchException(Request $request, $e)
    {
        if ($request->isXmlHttpRequest()) {
            return response()->json(
                ['error' => 'Twoja sesja wygasła. Proszę odświeżyć stronę i spróbować ponownie.'],
                $this->isHttpException($e) ? $e->getStatusCode() : 500
            );
        }

        return redirect($request->fullUrl())
            ->withInput($request->except('_token'))
            ->with('error', 'Wygląda na to, że nie wysłałeś tego formularza przez dłuższy czas. Spróbuj ponownie!');
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
     * @param Request $request
     * @param HttpException|ModelNotFoundException $e
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|SymfonyResponse
     */
    protected function renderHttpErrorException(Request $request, $e)
    {
        // Case insensitive path lookup.
        // Redirect to correct version if exists
        $path = $this->findCamelCasePath($request);

        if ($path === null) {
            return parent::render($request, $e);
        }

        return redirect($path, 301);
    }

    /**
     * Case insensitive path lookup.
     *
     * @param Request $request
     * @return null|string
     */
    protected function findCamelCasePath(Request $request): ?string
    {
        // try to find correct path for given page
        $path = rawurldecode(rtrim($request->getPathInfo(), '/'));
        $page = $this->container[PageRepositoryInterface::class]->findByPath($path);

        return $page !== null && $page->path !== $path ? $page->path : null;
    }

    /**
     * Get the html response content.
     *
     * @param  \Exception  $e
     * @throws \FlattenException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertExceptionToResponse(Exception $e)
    {
        if (config('app.debug')) {
            return parent::convertExceptionToResponse($e);
        }

        $e = FlattenException::create($e);

        // on production site, we MUST render "nice" error page
        return SymfonyResponse::create(view('errors.500')->render(), $e->getStatusCode(), $e->getHeaders());
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
