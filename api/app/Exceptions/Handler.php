<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Traits\RestExceptionHandlerTrait;
use App\Exceptions\BucketNotFoundException;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\TenantDomainNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Exceptions\SamlException;
use Throwable;
use InvalidArgumentException;

class Handler extends ExceptionHandler
{
    use RestExceptionHandlerTrait;
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
        SamlException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $exception)
    {
        if (env('APP_ENV') === 'local' && env('APP_DEBUG')) {
            dd($exception);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->methodNotAllowedHttp();
        }
        if ($exception instanceof BucketNotFoundException) {
            return $this->bucketNotFound($exception->getCode(), $exception->getMessage());
        }
        if ($exception instanceof FileNotFoundException) {
            return $this->filenotFound($exception->getCode(), $exception->getMessage());
        }
        if ($exception instanceof TenantDomainNotFoundException) {
            return $this->tenantDomainNotFound($exception->getCode(), $exception->getMessage());
        }
        if ($exception instanceof SamlException) {
            return $this->samlError($exception->getCode(), $exception->getMessage());
        }
        if ($exception instanceof InvalidArgumentException) {
            return $this->invalidArgument($exception->getCode(), $exception->getMessage());
        }

        return $this->internalServerError(trans('messages.custom_error_message.ERROR_INTERNAL_SERVER_ERROR'));
    }
}
