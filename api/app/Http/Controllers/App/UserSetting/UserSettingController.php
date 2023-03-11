<?php
namespace App\Http\Controllers\App\UserSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use App\Helpers\Helpers;
use App\Repositories\User\UserRepository;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Events\User\UserActivityLogEvent;
use Illuminate\Validation\Rule;
use App\Repositories\UserSetting\UserSettingRepository;
use App\Repositories\Timezone\TimezoneRepository;
use App\Helpers\LanguageHelper;
use App\Factories\JWTCookieFactory;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;

//!  User Setting controller
/*!
This controller is responsible for handling user settings operation.
 */
class UserSettingController extends Controller
{
    use RestExceptionHandlerTrait;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Repositories\User\UserRepository
     */
    private $userRepository;

    /**
     * @var App\Repositories\UserSetting\UserSettingRepository
     */
    private $userSettingRepository;

    /**
     * @var App\Repositories\Timezone\TimezoneRepository
     */
    private $timeZoneRepository;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * Create a new controller instance.
     *
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Helpers\Helpers $helpers
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Repositories\UserSetting\UserSettingRepository $userSettingRepository
     * @param App\Repositories\Timezone\TimezoneRepository $timeZoneRepository
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @return void
     */
    public function __construct(
        ResponseHelper $responseHelper,
        Helpers $helpers,
        UserRepository $userRepository,
        UserSettingRepository $userSettingRepository,
        TimezoneRepository $timeZoneRepository,
        LanguageHelper $languageHelper,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository
    ) {
        $this->responseHelper = $responseHelper;
        $this->helpers = $helpers;
        $this->userRepository = $userRepository;
        $this->userSettingRepository = $userSettingRepository;
        $this->timeZoneRepository = $timeZoneRepository;
        $this->languageHelper = $languageHelper;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
    }

    /**
    * Store user settings
    *
    * @param Request $request
    * @return Illuminate\Http\JsonResponse
    */
    public function store(Request $request) : JsonResponse
    {
        $tenant = $this->helpers->getTenantIdAndSponsorIdFromRequest($request);
        $tenantId = $tenant->tenant_id;
        $isDonationSettingEnabled = $this->tenantActivatedSettingRepository
            ->checkTenantSettingStatus(
                config('constants.tenant_settings.DONATION_MISSION'),
                $request
            );

        $validatorArray = [
            'old_password' => 'required_with:password,confirm_password',
            'password' => 'required_with:old_password,confirm_password|min:8|regex:/[0-9]/','regex:/[a-z]/','regex:/[A-Z]/',
            'confirm_password' => 'required_with:old_password,password|min:8|same:password',
            'language_id' => 'required',
            'timezone_id' => 'required|exists:timezone,timezone_id,deleted_at,NULL',
        ];
        if ($isDonationSettingEnabled) {
            $validatorArray['currency'] = 'required|regex:/^[A-Z]{3}$/';
        }
        $validator = Validator::make(
            $request->toArray(),
            $validatorArray,
            [
                'password.regex' => trans('messages.custom_error_message.ERROR_PASSWORD_VALIDATION_MESSAGE')
            ]
        );

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_INVALID_DETAIL'),
                $validator->errors()->first()
            );
        }

        $apiData = [];
        if (!empty($request->old_password) && !empty($request->auth->password)) {
            $isValidOldPassword = Hash::check($request->old_password, $request->auth->password);
            if (!$isValidOldPassword) {
                return $this->responseHelper->error(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                    config('constants.error_codes.ERROR_INVALID_DETAIL'),
                    trans('messages.custom_error_message.ERROR_OLD_PASSWORD_NOT_MATCHED')
                );
            }

            // Update password
            $passwordChange = $this->userRepository->changePassword($request->auth->user_id, $request->password);

            // Get new token
            $tenantName = $this->helpers->getSubDomainFromRequest($request);
            $newToken = ($passwordChange) ? $this->helpers->getJwtToken($request->auth->user_id, $tenantName) : '';

            // As we don't use HTTPS on the local stack, it's not possible to use secured cookies on this environment
            $isSecuredCookie = config('app.env') !== 'local';

            // Create the cookie holding the token
            $cookie = JWTCookieFactory::make($newToken, config('app.url'), $isSecuredCookie);

            // Make activity log
            event(new UserActivityLogEvent(
                config('constants.activity_log_types.AUTH'),
                config('constants.activity_log_actions.PASSWORD_UPDATED'),
                config('constants.activity_log_user_types.REGULAR'),
                $request->auth->email,
                get_class($this),
                null,
                $request->auth->user_id
            ));
        }

        $userData = [
            'language_id' => $request->get('language_id'),
            'timezone_id' => $request->get('timezone_id')
        ];

        if ($isDonationSettingEnabled) {
            $userData['currency'] = $request->get('currency');
        }

        // Update language, timezone and currency data
        $this->userSettingRepository->saveUserData($request->auth->user_id, $userData);

        // Store Activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.USER_PROFILE'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $request->except(['password', 'old_password', 'confirm_password']),
            $request->auth->user_id,
            $request->auth->user_id
        ));

        // Send response
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_SETTING_UPDATE_SUCCESS');

        if (!empty($cookie)) {
            return $this->responseHelper->success($apiStatus, $apiMessage, $apiData)->withCookie($cookie);
        }
        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
    * Get user settings
    *
    * @param Request $request
    * @return Illuminate\Http\JsonResponse
    */
    public function index(Request $request) : JsonResponse
    {
        $userId = $request->auth->user_id;
        $apiData = [];
        $apiData['preference'] = $this->userSettingRepository->getUserPreferenceData($userId);
        $timezoneList = $this->timeZoneRepository->getTimezoneList();
        $apiData['timezone'] = $timezoneList->toArray();
        $apiData['languages'] = $this->languageHelper->getTenantLanguages($request);
        $getTenantCurrency = $this->helpers->getTenantActivatedCurrencies($request);
        $apiData['currencies'] = $getTenantCurrency->toArray();
        $userData = $this->userRepository->findUserDetail($userId);
        $apiData['linked_in_url'] = $userData['linked_in_url'];

        // Send response
        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_SETTING_LISTING');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }
}
