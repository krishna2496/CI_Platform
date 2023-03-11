<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;

class LocalizationMiddleware
{
    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * Create a new localization middleware instance.
     *
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Helpers\ResponseHelper $responseHelper
     * @return void
     */
    public function __construct(LanguageHelper $languageHelper, ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
    }

    /**
     * Handle an incoming request.
     *
     * @param object $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Set localization to config locale
        config(['app.locale' => $request->header('X-localization')]);

        try {
            // Get tenant language base on localization or default language of tenant from database
            $language = $this->languageHelper->checkTenantLanguage($request);
        } catch (\Exception $e) {
            // Send authentication error response if api user not found in master database
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_INVALID_API_AND_SECRET_KEY'),
                trans('messages.custom_error_message.ERROR_INVALID_API_AND_SECRET_KEY')
            );
        }

        // set laravel localization
        app('translator')->setLocale($language->code);
        config(['app.locale' => $language->code]);

        // continue request
        return $next($request);
    }
}
