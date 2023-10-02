<?php

namespace Coyote\Exceptions;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

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
     * @param  \Exception $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception) && app()->bound('sentry') && app()->environment('production')) {
            // send report to sentry
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    protected function context()
    {
        return array_merge(parent::context(), ['url' => request()->url(), 'ip' => request()->ip()]);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            return $this->renderTokenMismatchException($request, $exception);
        } elseif (($exception instanceof HttpException && $exception->getStatusCode() === 404) || $exception instanceof ModelNotFoundException) {
            return $this->renderHttpErrorException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param Request $request
     * @param TokenMismatchException $e
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function renderTokenMismatchException(Request $request, $e)
    {
        if ($request->expectsJson()) {
            return response()->json(
                ['message' => 'Twoja sesja wygasła. Proszę odświeżyć stronę i spróbować ponownie.'],
                $this->isHttpException($e) ? $e->getStatusCode() : 500
            );
        }

        return redirect($request->fullUrl())
            ->withInput($request->except('_token'))
            ->with('error', 'Wygląda na to, że nie wysłałeś tego formularza przez dłuższy czas. Spróbuj ponownie!');
    }

    /**
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|SymfonyResponse
     */
    protected function renderHttpErrorException(Request $request, $e)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Uuups. Strona nie istnieje lub została usunięta.'], 404);
        }

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
}
