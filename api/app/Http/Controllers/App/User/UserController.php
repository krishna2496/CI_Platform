<?php
namespace App\Http\Controllers\App\User;

use App\Transformations\UserTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\LanguageHelper;
use App\Helpers\Helpers;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\User\UserRepository;
use App\Repositories\UserCustomField\UserCustomFieldRepository;
use App\Repositories\UserFilter\UserFilterRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\City\CityRepository;
use App\Helpers\ResponseHelper;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use Illuminate\Validation\Rule;
use App\Helpers\S3Helper;
use App\Events\User\UserActivityLogEvent;
use App\Transformations\CityTransformable;
use App\Models\TenantOption;
use App\Notifications\InviteUser;
use Carbon\Carbon;
use App\Services\UserService;
use App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository;


class UserController extends Controller
{
    use RestExceptionHandlerTrait, UserTransformable, CityTransformable;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserCustomFieldRepository
     */
    private $userCustomFieldRepository;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    /**
     * @var ResponseHelper
     */
    private $responseHelper;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var Helpers
     */
    private $helpers;

    /**
     * @var S3Helper
     */
    private $s3helper;

    /**
     * @var UserFilterRepository
     */
    private $userFilterRepository;

    /**
     * The response instance.
     *
     * @var TenantOptionRepository
     */
    private $tenantOptionRepository;

    /**
     * @var App\Services\UserService
     */
    private $userService;

    /**
     * @var App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository
     */
    private $tenantActivatedSettingRepository;

    /**
     * Create a new controller instance.
     *
     * @param App\Repositories\User\UserRepository $userRepository
     * @param App\Repositories\UserCustomField\UserCustomFieldRepository $userCustomFieldRepository
     * @param App\Repositories\City\CityRepository $cityRepository
     * @param Illuminate\Http\UserFilterRepository $userFilterRepository
     * @param Illuminate\Http\ResponseHelper $responseHelper
     * @param App\Helpers\LanguageHelper $languageHelper
     * @param App\Helpers\Helpers $helpers
     * @param App\Helpers\S3Helper $s3helper
     * @param App\Repositories\TenantOption\TenantOptionRepository $tenantOptionRepository
     * @param App\Services\UserService $userService
     * @param App\Repositories\TenantActivatedSetting\TenantActivatedSettingRepository $tenantActivatedSettingRepository
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        UserCustomFieldRepository $userCustomFieldRepository,
        CityRepository $cityRepository,
        UserFilterRepository $userFilterRepository,
        ResponseHelper $responseHelper,
        LanguageHelper $languageHelper,
        Helpers $helpers,
        S3Helper $s3helper,
        TenantOptionRepository $tenantOptionRepository,
        UserService $userService,
        TenantActivatedSettingRepository $tenantActivatedSettingRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userCustomFieldRepository = $userCustomFieldRepository;
        $this->cityRepository = $cityRepository;
        $this->userFilterRepository = $userFilterRepository;
        $this->responseHelper = $responseHelper;
        $this->languageHelper = $languageHelper;
        $this->helpers = $helpers;
        $this->s3helper = $s3helper;
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->userService = $userService;
        $this->tenantActivatedSettingRepository = $tenantActivatedSettingRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userList = $request->has('search')
            ? $this->userRepository->searchUsers($request->input('search'), $request->auth->user_id)
            : $this->userRepository->listUsers($request->auth->user_id);

        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $users = $userList->map(function (User $user) use ($request, $tenantName) {
            $user = $this->transformUser($user, $tenantName);
            return $user;
        })->all();

        $apiMessage = (empty($users))
            ? trans('messages.success.MESSAGE_NO_RECORD_FOUND')
            : trans('messages.success.MESSAGE_USER_LISTING');

        return $this->responseHelper->success(Response::HTTP_OK, $apiMessage, $users);
    }

    /**
     * Get default language of user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserDefaultLanguage(Request $request): JsonResponse
    {
        try {
            $email = $request->get('email');
            $user = $this->userRepository->getUserByEmail($email);

            $userLanguage['default_language_id'] = $user->language_id;

            return $this->responseHelper->success(Response::HTTP_OK, '', $userLanguage);
        } catch (ModelNotFoundException $e) {
            return $this->modelNotFound(
                config('constants.error_codes.ERROR_USER_NOT_FOUND'),
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
    }

    /**
     * Get user detail.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $cityList = collect();
        $userId = $request->auth->user_id;
        $userDetail = $this->userRepository->findUserDetail($userId);
        $customFields = $this->userCustomFieldRepository->getUserCustomFields($request);
        $userSkillList = $this->userRepository->userSkills($userId);
        if (isset($userDetail->country_id) && $userDetail->country_id != 0) {
            $cityList = $this->cityRepository->cityList($userDetail->country_id);
        }
        $tenantLanguages = $this->languageHelper->getTenantLanguageList($request);
        $tenantLanguageCodes = $this->languageHelper->getTenantLanguageCodeList($request);
        $availabilityList = $this->userRepository->getAvailability();

        $defaultLanguage = $this->languageHelper->getDefaultTenantLanguage($request);
        $languages = $this->languageHelper->getLanguages();
        $language = config('app.locale') ?? $defaultLanguage->code;
        $languageCode = $languages->where('code', $language)->first()->code;

        $userDetail->language_id = (is_null($userDetail->language_id) || $userDetail->language_id == 0)
        ? $defaultLanguage->language_id : $userDetail->language_id;

        $userLanguageCode = $languages->where('language_id', $userDetail->language_id)->first()->code;
        $userCustomFieldData = [];
        $userSkillData = [];
        $customFieldsData = $customFields->toArray();

        $customFieldsValue = $userDetail->userCustomFieldValue;
        unset($userDetail->userCustomFieldValue);

        if (!empty($customFieldsData) && (isset($customFieldsData))) {
            $returnData = [];
            foreach ($customFieldsData as $key => $value) {
                if ($value) {
                    $arrayKey = array_search($languageCode, array_column($value['translations'], 'lang'));
                    $returnData = $value;
                    unset($returnData['translations']);
                    if (isset($value['translations'][$arrayKey])) {
                        if ($arrayKey !== '') {
                            $returnData['translations']['lang'] = $value['translations'][$arrayKey]['lang'];
                            $returnData['translations']['name'] = $value['translations'][$arrayKey]['name'];
                            if (isset($value['translations'][$arrayKey]['values'])) {
                                $returnData['translations']['values'] = $value['translations'][$arrayKey]['values'];
                            }

                            $userCustomFieldValue = $customFieldsValue->where('field_id', $value['field_id'])
                            ->where('user_id', $userId)->first();
                            $returnData['user_custom_field_value'] = $userCustomFieldValue->value ?? '';
                        }
                    }
                }
                if (!empty($returnData)) {
                    $userCustomFieldData[] = $returnData;
                }
            }
        }

        if (!empty($userSkillList) && (isset($userSkillList))) {
            $returnData = [];
            foreach ($userSkillList as $key => $value) {
                if ($value['skill']) {
                    $arrayKey = array_search($languageCode, array_column($value['skill']['translations'], 'lang'));
                    if ($arrayKey !== '') {
                        $returnData[config('constants.SKILL')][$key]['skill_id'] =
                        $value['skill']['skill_id'];
                        $returnData[config('constants.SKILL')][$key]['skill_name'] =
                        $value['skill']['skill_name'];
                        $returnData[config('constants.SKILL')][$key]['translations'] =
                        $value['skill']['translations'][$arrayKey]['title'];
                    }
                }
            }
            if (!empty($returnData)) {
                $userSkillData = $returnData[config('constants.SKILL')];
            }
        }

        $availabilityData = [];
        foreach ($availabilityList as $availability) {
            $arrayKey = array_search($languageCode, array_column($availability['translations'], 'lang'));
            if ($arrayKey  !== '' && isset($availability['translations'][$arrayKey]['title'])) {
                $availabilityData[$availability['availability_id']] = $availability
                ['translations'][$arrayKey]['title'];
            }
        }
        $availabilityList = $availabilityData;
        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        // Get tenant default language
        $defaultTenantLanguage = $this->languageHelper->getDefaultTenantLanguage($request);

        // Get language id
        $languageId = $this->languageHelper->getLanguageId($request);
        if (!$cityList->isEmpty()) {
            // Transform city details
            $cityList = $this->cityTransform($cityList->toArray(), $languageId, $defaultTenantLanguage->language_id);
        }

        $userDonationGoal = $this->userRepository->getUserDonationGoal($userId);

        $apiData = $userDetail->toArray();
        $apiData['language_code'] = $userLanguageCode;
        $apiData['avatar'] = ((isset($apiData['avatar'])) && $apiData['avatar'] !="") ? $apiData['avatar'] :
        $this->helpers->getUserDefaultProfileImage($tenantName);
        $apiData['custom_fields'] = $userCustomFieldData;
        $apiData['user_skills'] = $userSkillData;
        $apiData['city_list'] = $cityList;
        $apiData['language_list'] = $tenantLanguages;
        $apiData['language_code_list'] = $tenantLanguageCodes;
        $apiData['availability_list'] = $availabilityList;
        $apiData['user_donation_goal'] = $userDonationGoal;

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_USER_FOUND');

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Update user data
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $id = $request->auth->user_id;

        $rules = [
            'first_name' => 'required|max:60',
            'last_name' => 'required|max:60',
            'employee_id' => [
                'max:60',
                'nullable',
                Rule::unique('user')->ignore($id, 'user_id,deleted_at,NULL')
            ],
            'department' => 'max:60',
            'linked_in_url' => 'url|valid_linkedin_url',
            'availability_id' => 'integer|exists:availability,availability_id,deleted_at,NULL',
            'city_id' => 'sometimes|integer|exists:city,city_id,deleted_at,NULL',
            'country_id' => 'required|integer|exists:country,country_id,deleted_at,NULL',
            'custom_fields.*.field_id' => 'sometimes|required|exists:user_custom_field,field_id,deleted_at,NULL',
            'skills' => 'array',
            'skills.*.skill_id' => 'required_with:skills|integer|exists:skill,skill_id,deleted_at,NULL',
            'title' => 'max:60',
            'language_id' => 'sometimes|required',
            'timezone_id' => 'sometimes|required|exists:timezone,timezone_id,deleted_at,NULL'
        ];

        $isDonationSettingEnabled = $this->tenantActivatedSettingRepository
            ->checkTenantSettingStatus(
                config('constants.tenant_settings.DONATION_MISSION'),
                $request
            );
        if ($isDonationSettingEnabled) {
            $rules['donation_goal'] = 'required_with:donation_goal_year|integer|min:1|max:99999999';
            $rules['donation_goal_year'] = 'required_with:donation_goal|integer|digits:4';
        }

        $validator = Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        if (isset($request->language_id) && !$this->languageHelper->validateLanguageId($request)) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                trans('messages.custom_error_message.ERROR_USER_INVALID_LANGUAGE')
            );
        }
        if (!empty($request->skills) && count($request->skills) > config('constants.SKILL_LIMIT')) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_SKILL_LIMIT'),
                trans('messages.custom_error_message.ERROR_SKILL_LIMIT')
            );
        }

        if (isset($request->status)) {
            $data['status'] = ($request->status) ? config('constants.user_statuses.ACTIVE') : config('constants.user_statuses.INACTIVE');
            $request->merge($data);
        }
        $request->replace($request->except(['email', 'is_admin', 'expiry']));

        $this->userFilterRepository->saveFilter($request);
        $userDetail = $this->userService->findById($id);
        $data = $request->all();

        if ($userDetail->pseudonymize_at && $userDetail->pseudonymize_at !== '0000-00-00 00:00:00') {
            $data = $this->userService->unsetPseudonymizedFields($data);
        }
        // Set user status to inactive when pseudonymized
        if (($userDetail->pseudonymize_at === '0000-00-00 00:00:00' || $userDetail->pseudonymize_at === null) &&
            array_key_exists('pseudonymize_at', $data)
        ) {
            $data['status'] = config('constants.user_statuses.INACTIVE');
        }

        // Update user
        $user = $this->userService->update($data, $id);
        $userData = $this->userRepository->checkProfileCompleteStatus($user->user_id, $request);

        // Update user skills
        if (!empty($request->skills)) {
            $this->userService->updateSkill($data, $id);
        }

        if ($isDonationSettingEnabled && $request->has('donation_goal') && $request->has('donation_goal_year')) {
            $this->userRepository->updateDonationGoal($request);
        }

        $this->helpers->syncUserData($request, $user);

        // Store Activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.USER_PROFILE'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $request->except(['password']),
            $request->auth->user_id,
            $user->user_id
        ));

        return $this->responseHelper->success(
            Response::HTTP_OK,
            trans('messages.success.MESSAGE_USER_UPDATED'),
            ['user_id' => $user->user_id, 'is_profile_complete' => $userData->is_profile_complete]
        );
    }

    /**
     * Upload profile image of user
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function uploadProfileImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->toArray(), [
            'avatar' => 'required|valid_profile_image'
        ]);

        // If request parameter have any error
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $validator->errors()->first()
            );
        }

        $userId = $request->auth->user_id;
        $tenantName = $this->helpers->getSubDomainFromRequest($request);
        $avatar = preg_replace('#^data:image/\w+;base64,#i', '', $request->avatar);
        $imagePath = $this->s3helper->uploadProfileImageOnS3Bucket($avatar, $tenantName, $userId);

        $this->userRepository->update(['avatar' => $imagePath], $userId);

        $apiData = ['avatar' => $imagePath];
        $apiMessage = trans('messages.success.MESSAGE_PROFILE_IMAGE_UPLOADED');
        $apiStatus = Response::HTTP_OK;

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.USER_PROFILE_IMAGE'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $apiData,
            $request->auth->user_id,
            $request->auth->user_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * store cookie agreement date
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function saveCookieAgreement(Request $request): JsonResponse
    {
        $userId = $request->auth->user_id;

        // Update cookie agreement date
        $this->userRepository->updateCookieAgreement($userId);

        // Set response data
        $apiData = ['user_id' => $userId];
        $apiStatus = Response::HTTP_CREATED;
        $apiMessage = trans('messages.success.MESSAGE_USER_COOKIE_AGREEMENT_ACCEPTED');

        // Make activity log
        event(new UserActivityLogEvent(
            config('constants.activity_log_types.USER_COOKIE_AGREEMENT'),
            config('constants.activity_log_actions.ACCEPTED'),
            config('constants.activity_log_user_types.REGULAR'),
            $request->auth->email,
            get_class($this),
            $apiData,
            $request->auth->user_id,
            $request->auth->user_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage, $apiData);
    }

    /**
     * Create Password - Send create password link to user's email address
     *
     * @param App\User $user
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function inviteUser(User $user, Request $request): JsonResponse
    {
        $samlSettings = $this->tenantOptionRepository->getOptionValue(TenantOption::SAML_SETTINGS);

        if (
            $samlSettings
            && count($samlSettings)
            && $samlSettings[0]['option_value']
            && $samlSettings[0]['option_value']['saml_access_only']
        ) {
            return $this->responseHelper->error(
                Response::HTTP_BAD_REQUEST,
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                config('constants.error_codes.ERROR_SAML_ACCESS_ONLY_ACTIVE'),
                trans('messages.custom_error_message.ERROR_SAML_ACCESS_ONLY_ACTIVE')
            );
        }

        // Server side validations
        $validator = Validator::make($request->toArray(), [
            'email' => 'required|email',
            'subject' => 'required',
            'body' => 'required',
            'language' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVITE_INVALID_DATA'),
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

        if ($userDetail->status === config('constants.user_statuses.ACTIVE')) {
            return $this->responseHelper->error(
                Response::HTTP_BAD_REQUEST,
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                config('constants.error_codes.ERROR_USER_ACTIVE'),
                trans('messages.custom_error_message.ERROR_USER_ACTIVE')
            );
        }

        if ($userDetail->expiry) {
            $userExpirationDate = new \DateTime($userDetail->expiry);
            if ($userExpirationDate < new \DateTime()) {
                return $this->responseHelper->error(
                    Response::HTTP_BAD_REQUEST,
                    Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                    config('constants.error_codes.ERROR_ACCOUNT_EXPIRED'),
                    trans('messages.custom_error_message.ERROR_ACCOUNT_EXPIRED')
                );
            }
        }

        $tenantLogo = $this->tenantOptionRepository->getOptionValueFromOptionName('custom_logo');

        $details = [
            'subject' => $request->get('subject'),
            'body' => $request->get('body'),
            'company_logo' => $tenantLogo->option_value,
            'language' => $request->get('language'),
        ];

        try {
            $userDetail->notify(new InviteUser($details));
        } catch (\Exception $e) {
            return $this->responseHelper->error(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
                config('constants.error_codes.ERROR_SEND_USER_INVITE_LINK'),
                trans('messages.custom_error_message.ERROR_SEND_USER_INVITE_LINK')
            );
        }

        $userDetail->status = config('constants.user_statuses.ACTIVE');
        $userDetail->invitation_sent_at = Carbon::now()->toDateTimeString();
        $userDetail->save();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_USER_INVITE_LINK_SEND_SUCCESS');

        event(new UserActivityLogEvent(
            config('constants.activity_log_types.AUTH'),
            config('constants.activity_log_actions.UPDATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $userDetail->email,
            get_class($this),
            $request->only('email', 'subject', 'language'),
            $userDetail->user_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }

    /**
     * Set password triggered by the send invite action
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function createPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->toArray(), [
                'email' => 'required|email',
                'password' => [
                    'required',
                    'min:8',
                    'regex:/[0-9]/',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/'
                ]
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

        $userDetail = $this->userRepository->findUserByEmail($request->get('email'));

        if (!$userDetail) {
            return $this->responseHelper->error(
                Response::HTTP_NOT_FOUND,
                Response::$statusTexts[Response::HTTP_NOT_FOUND],
                config('constants.error_codes.ERROR_EMAIL_NOT_EXIST'),
                trans('messages.custom_error_message.ERROR_EMAIL_NOT_EXIST')
            );
        }

        $userDetail->password = $request->get('password');
        $userDetail->save();

        $apiStatus = Response::HTTP_OK;
        $apiMessage = trans('messages.success.MESSAGE_PASSWORD_CHANGE_SUCCESS');

        event(new UserActivityLogEvent(
            config('constants.activity_log_types.AUTH'),
            config('constants.activity_log_actions.PASSWORD_UPDATED'),
            config('constants.activity_log_user_types.REGULAR'),
            $userDetail->email,
            get_class($this),
            $request->only('email'),
            $userDetail->user_id
        ));

        return $this->responseHelper->success($apiStatus, $apiMessage);
    }
}
