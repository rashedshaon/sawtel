<?php namespace Vdomah\JWTAuth\Classes;

use October\Rain\Foundation\Exception\Handler;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Response;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

/* Custom error handler which replaces the default error handler for OctoberCMS. */
class CustomHandler extends Handler
{
    /**
    * Render an exception into an HTTP response.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Exception  $exception
    * @return \Illuminate\Http\Response
    */
    public function render($request, Exception $exception)
    {
        // dd($exception instanceof UnauthorizedHttpException);
        // Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
        /* Custom JSON response for ModelNotFoundException exceptions. */
        // if($exception instanceof UnauthorizedHttpException)
        // {
        //     $code = 0;
        //     if($exception->getMessage() == 'Token has expired')
        //     {
        //         $code = 1;
        //     }
        //     return Response::json(['status' => 'error', 'msg' => $exception->getMessage(), 'code' => $code]);
        // }
        if($exception instanceof TokenInvalidException)
        {
            return Response::json(['status' => 'error', 'msg' => $exception->getMessage(), 'code' => $exception->getCode()]);
        }
        // if($exception instanceof TokenExpiredException)
        // {
        //     return Response::json(['status' => 'error', 'msg' => $exception->getMessage(), 'code' => 3]);
        // }
        
        
        /* The rest of this code is just the 'default' code from OctoberCMS' error handler. */
        /* The only change is the code above this comment where I handle a specific exception in a unique way.*/
        /* i.e. I decided to return JSON for the error rather than an HTML error page (in debug mode). */
        if (!class_exists('Event')) {
            return parent::render($request, $exception);
        }

        $statusCode = $this->getStatusCode($exception);
        $response = $this->callCustomHandlers($exception);

        if (!is_null($response)) {
            return Response::make($response, $statusCode);
        }

        if ($event = Event::fire('exception.beforeRender', [$exception, $statusCode, $request], true)) {
            return Response::make($event, $statusCode);
        }

        return parent::render($request, $exception);
    }
}