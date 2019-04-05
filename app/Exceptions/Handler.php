<?php

namespace ElectronicInvoicing\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Str;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException && $exception->getCode() == 9) {
            return;
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (($request->wantsJson() || $request->expectsJson()) && Str::startsWith($request->decodedPath(), 'api/auth')) {
            if($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException){
                return response()->json([
                    'code' => 405,
                    'message' => 'Request method is not supported for the requested resource.',
                    'errors' => [
                        'error' => 'Method Not Allowed',
                        'info' => 'Check your request method.'
                    ]
                ], 405);
            } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'code' => 419,
                    'message' => 'Token is missing or expired.',
                    'errors' => [
                        'error' => 'Page Expired',
                        'info' => 'Use your credentials to generate a new token and retry.'
                    ]
                ], 419);
            }
            info('#### ERROR IN API REQUEST #######################');
            info(' CODE: ' . $exception->getCode());
            info(' FILE: ' . $exception->getFile());
            info(' LINE: ' . $exception->getLine());
            info(' MESSAGE: ' . $exception->getMessage());
            info('#### END ERROR IN API REQUEST ###################');
            return response()->json([
                'code' => 520,
                'message' => 'An unknown error has occurred.',
                'errors' => [
                    'error' => 'Unknown Error'
                ]
            ], 520);
        }
        /*if($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException){
            //return abort('404');
        } elseif ($exception instanceof \BadMethodCallException) {
            //return abort('500');
        }*/
        return parent::render($request, $exception);
    }
}
