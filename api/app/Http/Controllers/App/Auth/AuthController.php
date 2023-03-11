<?php

namespace App\Http\Controllers\App\Auth;

use App\Events\User\UserActivityLogEvent;
use App\Factories\JWTCookieFactory;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Config;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\TenantOption;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\User\UserRepository;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;
use Validator;

//!  Auth controller
/*!
This controller is responsible for handling authenticate, change password and reset password operations.
 */
class AuthController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * The response instance.
     *
     * @var App\Repositories\TenantOption\TenantOptionRepository
     */
    private $tenantOptionRepository;

    /*
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Models\PasswordReset
     */
    private $passwordReset;

    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Http\Request $request
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param App\Helpers\Helpers $helpers
     * @param Illuminate\Helpers\LanguageHelper $languageHelper
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Models\PasswordReset $passwordReset
     * @return void
     */
    public function __construct(
        Request $request,
        ResponseHelper $responseHelper,
        TenantOptionRepository $tenantOptionRepository,
        Helpers $helpers,
        LanguageHelper $languageHelper,
        UserRepository $userRepository,
        PasswordReset $passwordReset
    ) {
        $this->request = $request;
        $this->responseHelper = $responseHelper;
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->helpers = $helpers;
        $this->languageHelper = $languageHelper;
        $this->userRepository = $userRepository;
        $this->passwordReset = $passwordReset;
        $this->passwordBrokerManager = new PasswordBrokerManager(app());
    }

    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @param \App\User $user
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function authenticate(User $user, Request $request): JsonResponse
    {
        $samlSettings = $this->tenantOptionRepository->getOptionValue(
          TenantOption::SAML_SETTINGS
        );

        if ($samlSettings
            && count($samlSettings)
            && $samlSettings[0]['option_value']
            && $samlSettings[0]['option_value']['saml_access_only']
        ) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_UNAUTHORIZED_LOGIN_METHOD'),
                trans('messages.custom_error_message.ERROR_UNAUTHORIZED_LOGIN_METHOD')
            );
        }

        $validator = Validator::make($request->toArray(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_INVALID_EMAIL_OR_PASSWORD'),
                $validator->errors()->first()
            );
        }

        // Fetch user by email address
        $userDetail = $user->with('timezone')->where('email', $this->request->input('email'))->first();

        if (!$userDetail) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_INVALID_EMAIL_OR_PASSWORD'),
                trans('messages.custom_error_message.ERROR_INVALID_EMAIL_OR_PASSWORD')
            );
        }
        // Verify user's password
        if (!Hash::check($this->request->input('password'), $userDetail->password)) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_INVALID_EMAIL_OR_PASSWORD'),
                trans('messages.custom_error_message.ERROR_INVALID_EMAIL_OR_PASSWORD')
            );
        }

        if ($userDetail->status !== config('constants.user_statuses.ACTIVE')) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_USER_BLOCKED'),
                trans('messages.custom_error_message.ERROR_USER_BLOCKED')
            );
        }

        if ($userDetail->expiry) {
            $userExpirationDate = new DateTime($userDetail->expiry);
            if ($userExpirationDate < new DateTime()) {
                return $this->responseHelper->error(
                    Response::HTTP_FORBIDDEN,
                    Response::$statusTexts[Response::HTTP_FORBIDDEN],
                    config('constants.error_codes.ERROR_USER_EXPIRED'),
                    trans('messages.custom_error_message.ERROR_USER_EXPIRED')
                );
            }
        }

        if ($userDetail->timezone_id === 0) {
            $userDetail->is_profile_complete = '';
        }

        // Generate JWT token
        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        $data['user_id'] = isset($userDetail->user_id) ? $userDetail->user_id : '';
        $data['first_name'] = isset($userDetail->first_name) ? $userDetail->first_name : '';
        $data['last_name'] = isset($userDetail->last_name) ? $userDetail->last_name : '';
        $data['country_id'] = isset($userDetail->country_id) ? $userDetail->country_id : '';
        $data['avatar'] = ((isset($userDetail->avatar)) && $userDetail->avatar !="") ? $userDetail->avatar :
        $this->helpers->getUserDefaultProfileImage($tenantName);
        $data['cookie_agreement_date'] = isset($userDetail->cookie_agreement_date) ?
                                         $userDetail->cookie_agreement_date : '';
        $data['email'] = ((isset($userDetail->email)) && $userDetail->email !="") ? $userDetail->email : '';
        $data['timezone'] = ((isset($userDetail->timezone)) && $userDetail->timezone !="") ?
        $userDetail->timezone['timezone'] : '';
        $data['is_profile_complete'] = ((isset($userDetail->is_profile_complete))
        && $userDetail->is_profile_complete != "") ? $userDetail->is_profile_complete : '';

        $data['receive_email_notification'] = (
            (isset($userDetail->receive_email_notification)) && $userDetail->receive_email_notification !=""
        ) ?
        $userDetail->receive_email_notification : '0';
        $data['isSuccessfulLogin'] = true;

        $apiData = $data;
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_USER_LOGGED_IN');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.AUTH'),
            config('constants.activity_log_actions.LOGIN'),
            config('constants.activity_log_user_types.REGULAR'),
            $userDetail->email,
            get_class($this),
            null,
            $userDetail->user_id
        ));

        // As we don't use HTTPS on the local stack, it's not possible to use secured cookies on this environment
        $isSecuredCookie = config('app.env') !== 'local';

        // Create the cookie holding the token
        $jwtToken = $this->helpers->getJwtToken($userDetail->user_id, $tenantName);
        $cookie = JWTCookieFactory::make($jwtToken, config('app.url'), $isSecuredCookie);

        return $this->responseHelper
            ->success($apiStatus, $apiMessage, $apiData)
            ->withCookie($cookie);
    }

    /**
     * Forgot password - Send Reset password link to user's email address
     *
     * @param \App\User $user
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function requestPasswordReset(User $user, Request $request): JsonResponse
    {
        // Server side validataions
        $validator = Validator::make($request->toArray(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_RESET_PASSWORD_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        $userDetail = $this->userRepository->findUserByEmail($request->get('email'));

        if (!$userDetail) {
            return $this->responseHelper->error(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_EMAIL_NOT_EXIST'),
                trans('messages.custom_error_message.ERROR_EMAIL_NOT_EXIST')
            );
        }

        if ($userDetail->status !== config('constants.user_statuses.ACTIVE')) {
            return $this->responseHelper->error(
                Response::HTTP_FORBIDDEN,
                Response::$statusTexts[Response::HTTP_FORBIDDEN],
                config('constants.error_codes.ERROR_USER_BLOCKED'),
                trans('messages.custom_error_message.ERROR_USER_BLOCKED')
            );
        }

        if ($userDetail->expiry) {
            $userExpirationDate = new DateTime($userDetail->expiry);
            if ($userExpirationDate < new DateTime()) {
                return $this->responseHelper->error(
                    Response::HTTP_FORBIDDEN,
                    Response::$statusTexts[Response::HTTP_FORBIDDEN],
                    config('constants.error_codes.ERROR_USER_EXPIRED'),
                    trans('messages.custom_error_message.ERROR_USER_EXPIRED')
                );
            }
        }

        $language = $this->languageHelper->getLanguageDetails($request);
        $languageCode = $language->code;
        config(['app.user_language_code' => $languageCode]);

        $refererUrl = $this->helpers->getRefererFromRequest($request);
        config(['app.mail_url' => $refererUrl.'/reset-password/']);

        //set tenant logo
        $tenantLogo = $this->tenantOptionRepository->getOptionWithCondition(['option_name' => 'custom_logo']);
        config(['app.tenant_logo' => $tenantLogo->option_value]);

        // Verify email address and send reset password link
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        // If reset password link didn't sent
        if (!$response === $this->passwordReset->RESET_LINK_SENT) {
            return $this->responseHelper->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_SEND_RESET_PASSWORD_LINK'),
                trans('messages.custom_error_message.ERROR_SEND_RESET_PASSWORD_LINK')
            );
        }

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_PASSWORD_RESET_LINK_SEND_SUCCESS');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.AUTH'),
            config('constants.activity_log_actions.PASSWORD_RESET_REQUEST'),
            config('constants.activity_log_user_types.REGULAR'),
            $userDetail->email,
            get_class($this),
            $request->toArray(),
            $userDetail->user_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * reset_password_link_expiry - check is reset password link is expired or not
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function passwordReset(Request $request): JsonResponse
    {
        $request->merge(['token'=>$request->reset_password_token]);

        // Server side validataions
        $validator = Validator::make($request->toArray(), [
                'email' => 'required|email',
                'token' => 'required',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/[0-9]/',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/'
                ],
                'password_confirmation' => 'required|min:8|same:password'
            ],
            ['password.regex' => trans('messages.custom_error_message.ERROR_PASSWORD_VALIDATION_MESSAGE')]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_DETAIL'),
                $validator->errors()->first()
            );
        }

        //get record of user by checking password expiry time
        $record = $this->passwordReset->where('email', $request->get('email'))
        ->where(
            'created_at',
            '>',
            Carbon::now()->subHours(config('constants.FORGOT_PASSWORD_EXPIRY_TIME'))
        )->first();

        //if record not found
        if (!$record) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_RESET_PASSWORD_LINK'),
                trans('messages.custom_error_message.ERROR_INVALID_RESET_PASSWORD_LINK')
            );
        }
        if (!Hash::check($request->get('token'), $record->token)) {
            //invalid hash
            return $this->responseHelper->error(
                Response::HTTP_UNAUTHORIZED,
                Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
                config('constants.error_codes.ERROR_INVALID_RESET_PASSWORD_LINK'),
                trans('messages.custom_error_message.ERROR_INVALID_RESET_PASSWORD_LINK')
            );
        }

        // Reset the password
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_PASSWORD_CHANGE_SUCCESS');

        $userDetail = $this->userRepository->findUserByEmail($request->get('email'));

        // Remove password before logging it
        $request->request->remove("password");
        $request->request->remove("password_confirmation");

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.AUTH'),
            config('constants.activity_log_actions.PASSWORD_RESET'),
            config('constants.activity_log_user_types.REGULAR'),
            $userDetail->email,
            get_class($this),
            $request->toArray(),
            $userDetail->user_id
        ));
        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request): array
    {
        return $request->only('email', 'password', 'password_confirmation', 'token');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    public function broker()
    {
        return $this->passwordBrokerManager->broker();
    }
    

    public function transmute(Request $request)
    {
        $isSecuredCookie = config('app.env') !== 'local';
        $credentials = $this->helpers->decodeJwtToken($request->token);

        $newToken = $this->helpers->getJwtToken(
            $credentials->sub,
            $this->helpers->getSubDomainFromRequest($request)
        );

        $cookie = JWTCookieFactory::make($newToken, config('app.url'), $isSecuredCookie);

        return $this->responseHelper
            ->success(
                Response::HTTP_OK,
                trans('messages.success.MESSAGE_USER_LOGGED_IN')
            )
            ->withCookie($cookie);
    }

    public function logout()
    {
        return $this->responseHelper
            ->success(
                Response::HTTP_OK,
                'You were successfully logged out',
                []
            )
            ->withCookie(JWTCookieFactory::makeExpired());
    }
}
