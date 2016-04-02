<?php namespace Coyote\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

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
        // error handler to AJAX request
        if ($request->isXmlHttpRequest()) { // moze lepiej bedzie uzyc wantsJson()?
            $statusCode = 500;

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

            if ($this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();
            }

            return response()->json($response, $statusCode);
        }

        return parent::render($request, $e);
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
