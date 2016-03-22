<?php namespace Coyote\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
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
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        // error handler to JSON request
        if ($request->isXmlHttpRequest()) { // moze lepiej bedzie uzyc wantsJson()?
            $response = [
                'error' => 'Przepraszamy, ale coś poszło nie tak. Prosimy o kontakt z administratorem.'
            ];

            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['message'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            $statusCode = 500;

            if ($this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();
            }

            return response()->json($response, $statusCode);
        }

        // in debug mode, call parent error handler
        if (config('app.debug') || parent::isHttpException($e)) {
            return parent::render($request, $e);
        }

        // on production site, we MUST render "nice" error page
        return response()->view('errors.500', [], 500);
    }
}
