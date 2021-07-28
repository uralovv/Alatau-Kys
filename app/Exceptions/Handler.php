<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {

        if ($request->is('api/*')) {
            $errorResponse = [
                'error' => true,
                'message' => $e->getMessage()
            ];

            if ($e instanceof NotFoundHttpException) {
                $errorResponse['message'] = 'Запроса по данному адресу не существует!';
            }

            if (config('app.env') === 'local') {
                $errorResponse['file'] = $e->getFile();
                $errorResponse['line'] = $e->getLine();
                $errorResponse['trace'] = $e->getTrace();
            }

            return response()->json($errorResponse, $e->getCode() ?: 422);
        }

        return parent::render($request, $e);

    }

}
