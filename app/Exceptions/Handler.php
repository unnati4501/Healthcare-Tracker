<?php

namespace App\Exceptions;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\ApiLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Mail;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
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

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            $this->sendEmail($exception); // sends an email
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() && strpos($request->path(), 'api/') !== false) {
            $inputData = [];
            if ($this->guard()->check()) {
                $user                 = $this->guard()->user();
                $inputData['user_id'] = (!empty($user)) ? $user->getKey() : null;
            }
            $inputData['type']         = 'request';
            $inputData['route']        = $request->getPathInfo();
            $inputData['headers']      = $request->headers;
            $inputData['parameters']   = $request->parameters;
            $inputData['request_data'] = json_encode($request->all());

            $apiLog = ApiLog::create($inputData);

            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                // If model not found
                // collect response data and update api log model
                $response = $this->notFoundResponse("Sorry! Requested data not found");
                $apiLog->update(['response_data' => $response]);

                return $response;
            } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                // If route not found
                // collect response data and update api log model
                $response = $this->notFoundResponse("Sorry! Requested route not found");
                $apiLog->update(['response_data' => $response]);

                return $response;
            } elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
                // ValidationException
                // collect response data and update api log model
                $response = $this->invalidResponse($exception->errors(), "The given data is invalid.");
                $apiLog->update(['response_data' => $response]);

                return $response;
            } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                // collect response data and update api log model
                $response = $this->unauthorizedResponse('Your session has timed out. Please login again.');
                $apiLog->update(['response_data' => $response]);
                return $response;
            } elseif ($exception instanceof \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException) {
                // collect response data and update api log model
                $response = $this->underMaintenanceResponse();
                $apiLog->update(['response_data' => $response]);

                return $response;
            } elseif ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                $response = $this->tooManyAttemptsResponse();
                $apiLog->update(['response_data' => $response]);

                return $response;
            }
        }

        if ($exception instanceof ModelNotFoundException) {
            if (url()->previous() == url()->current()) {
                if ($request->route()->getName() == "admin.event.view") {
                    return \Redirect::route('admin.event.index')->withErrors(['error' => "Sorry! Requested data not found"]);
                } else {
                    return \Redirect::back()->withErrors(['error' => "Sorry! Requested data not found"]);
                }
            } else {
                return \Redirect::back()->withErrors(['error' => "Sorry! Requested data not found"]);
            }
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            // collect response data and update api log model
            abort(401);
        }

        return parent::render($request, $exception);
    }

    /**
     * Sends an email to the developer about the exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function sendEmail(Throwable $exception)
    {
        try {
            $e = FlattenException::createFromThrowable($exception);

            $handler = new HtmlErrorRenderer(true); // boolean, true raises debug flag...
            
            $html = $handler->getBody($e);

            if ($exception instanceof \Predis\PredisException) {
                $stream       = $exception->getConnection()->getResource();
                $errorMessage = var_export(stream_get_meta_data($stream), true);
                $html         = $html . $errorMessage;
                Log::debug('error for ' . app()->environment(), [__CLASS__, __FILE__, __LINE__, $stream]);
            }

            if (app()->environment() == 'local' || app()->environment() == 'dev' || app()->environment() == 'qa' || app()->environment() == 'performance' || app()->environment() == 'preprod') {
                Log::debug('error for ' . app()->environment(), [__CLASS__, __FILE__, __LINE__, $exception]);
            }

            if (app()->environment() == 'uat' || app()->environment() == 'production') {
                Log::debug('error for ' . app()->environment(), [__CLASS__, __FILE__, __LINE__, $exception]);
            }
        } catch (Throwable $ex) {
            Log::debug('error for ' . app()->environment(), [__CLASS__, __FILE__, __LINE__, $ex]);
        }
    }

    public function renderForConsole($output, Throwable $e)
    {
        $output->writeln('Something broke!');

        (new ConsoleApplication)->renderException($e, $output);
    }
}
