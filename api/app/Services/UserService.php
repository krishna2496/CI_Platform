<?php

namespace App\Services;

use App\Exceptions\MaximumUsersReachedException;
use App\Models\TenantOption;
use App\Repositories\User\UserRepository;
use App\Services\TenantOptionService;
use App\User;
use DB;
use Exception;
use Illuminate\Validation\Rule;
use Validator;
use App\Helpers\Helpers;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

class UserService
{
    /**
     * @var App\Helpers\Helpers
     */
    private $userRepository;

    /**
     * @var  TenantOptionService
     */
    private $tenantOptionService;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Helpers\ResponseHelper
     */
    private $responseHelper;

    const NULLABLE_FIELDS = [
        'employee_id',
        'department',
        'linked_in_url',
        'why_i_volunteer',
        'availability_id',
        'city_id',
        'country_id',
        'profile_text',
        'position'
    ];

    /**
     * Create a new controller instance.
     *
     * @param  UserRepository       $userRepository
     * @param  TenentOptionService  $tenentOptionService
     *
     * @return  void
     */
    public function __construct(
        UserRepository $userRepository,
        TenantOptionService $tenantOptionService,
        Helpers $helpers,
        ResponseHelper $responseHelper
    ) {
        $this->userRepository = $userRepository;
        $this->tenantOptionService = $tenantOptionService;
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Get specific user
     *
     * @param Int $userId
     *
     * @return App\User
     */
    public function findById($userId): User
    {
        return $this->userRepository->find($userId);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  array
     *
     * @return  User
     *
     * @throws  MaximumUsersReachedException
     */
    public function store(array $request): User
    {
        $maxUsers = $this->tenantOptionService->getOptionValueFromOptionName(TenantOption::MAXIMUM_USERS);
        if ($maxUsers && $maxUsers->option_value >= 0) {
            $userCount = $this->getUserCount(true);
            if ($userCount >= $maxUsers->option_value) {
                throw new MaximumUsersReachedException(
                    trans('messages.ERROR_MAXIMUM_USERS_REACHED'),
                    config('constants.error_codes.ERROR_MAXIMUM_USERS_REACHED')
                );
            }
        }

        $user = $this->userRepository->store($request);
        if (!empty($request['custom_fields']) && isset($request['custom_fields'])) {
            $this->updateCustomFieldsValue($request['custom_fields'], $user->user_id);
        }
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Array
     * @param  int
     *
     * @return  User
     */
    public function update(Array $request, int $id): User
    {
        $response = $this->userRepository->update($request, $id);
        if (!empty($request['custom_fields']) && isset($request['custom_fields'])) {
            $this->updateCustomFieldsValue($request['custom_fields'], $id);
        }
        return $response;
    }

    /**
     * Get specific user content statistics
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function statistics($user, $params = null): Array
    {
        // get all the timesheet stasts
        $stats = $this->userRepository->getStatistics($user, $params);

        $data = $stats
            ->first()
            ->toArray();

        // Add organization count
        $data['organization_count'] = $this->organizationCount($user, $params);

        return $data;
    }

    /**
     * Get user's volunteer summary
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function volunteerSummary($user, $params = null): Array
    {
        $data = $this->userRepository->volunteerSummary($user, $params);
        $summary = $data
            ->first()
            ->toArray();

        $missionCount = $this->getUserMissionCount($user, $params);
        $favoriteMission = $this->getUserFavoriteMission($user, $params);

        return array_merge($summary, $missionCount, $favoriteMission);
    }

    /**
     * Get user's missions
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    private function getUserMissionCount($user, $params): Array
    {
        $mission = $this->userRepository->getMissionCount($user, $params);
        return $mission->first()->toArray();
    }

    /**
     * Get user's favorite missions
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    private function getUserFavoriteMission($user, $params): Array
    {
        $favorite = $this->userRepository->getFavoriteMission($user, $params);
        return $favorite->first()->toArray();
    }

    /**
     * Get specific user organization count
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function organizationCount($user, $params = null): Int
    {
        $organization = $this->userRepository->getOrgCount($user, $params);

        return $organization
            ->pluck('organization_count')
            ->first();
    }

    /**
     * Get the number of users.
     *
     * @param  bool
     *
     * @return  int
     */
    public function getUserCount(
        bool $includeInactive = false,
        bool $includeAdmin = false
    ): int {
        return $this->userRepository->getUserCount(
            $includeInactive,
            $includeAdmin
        );
    }

    /**
     * Store a newly created resource into database
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    public function linkSkill($data, $userId)
    {
        return $this->userRepository->linkSkill($data, $userId);
    }

    /**
     * Delete skills by user ID then store a newly created one into database
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    public function updateSkill($data, $userId)
    {
        $this->userRepository->deleteSkills($userId);
        return $this->linkSkill($data, $userId);
    }

    /**
     * Add/Update user custom field value.
     *
     * @param array $customFieldsValue
     * @param int $id
     * @return null|App\Models\UserCustomFieldValue
     */
    public function updateCustomFieldsValue($customFieldsValue, $id)
    {
        return $this->userRepository->updateCustomFields($customFieldsValue, $id);
    }

    /**
     * Validate the user data that is passed in the request
     *
     * @param array $request
     * @param boolean $isAdminRequest
     * @return JsonResponse | boolean
     */
    public function validateFields($request, $id = null, $isAdminRequest = true)
    {
        $fields = [
            'first_name' => 'sometimes|required|max:60',
            'last_name' => 'sometimes|required|max:60',
            'email' => 'required|email|unique:user,email,NULL,user_id,deleted_at,NULL',
            'password' => 'required|min:8|regex:/[0-9]/|regex:/[a-z]/|regex:/[A-Z]/',
            'availability_id' => 'sometimes|required|integer|exists:availability,availability_id,deleted_at,NULL',
            'timezone_id' => 'sometimes|required|integer|exists:timezone,timezone_id,deleted_at,NULL',
            'language_id' => 'sometimes|required|int',
            'city_id' => 'sometimes|integer|exists:city,city_id,deleted_at,NULL',
            'country_id' => 'sometimes|required|integer|exists:country,country_id,deleted_at,NULL',
            'profile_text' => 'sometimes|required',
            'employee_id' => 'max:60|unique:user,employee_id,NULL,user_id,deleted_at,NULL',
            'department' => 'sometimes|required|max:60',
            'linked_in_url' => 'url|valid_linkedin_url',
            'why_i_volunteer' => 'sometimes|required',
            'expiry' => 'sometimes|date|nullable',
            'status' => ['sometimes', Rule::in(config('constants.user_statuses'))],
            'position' => 'sometimes|nullable',
            'title' => 'max:60'
        ];

        if (array_key_exists('skills', $request)) {
            $fields['skills'] = 'array';
            $fields['skills.*.skill_id'] = 'required_with:skills|integer|exists:skill,skill_id,deleted_at,NULL';
        }

        if (array_key_exists('custom_fields', $request)) {
            $fields['custom_fields'] = 'array';
            $fields['custom_fields.*.field_id'] = 'required|exists:user_custom_field,field_id,deleted_at,NULL';
        }

        if ($id !== null) {
            $fields['email'] = [
                'sometimes',
                'required',
                'email',
                Rule::unique('user')->ignore($id, 'user_id,deleted_at,NULL')
            ];
            $fields['employee_id'] = [
                'sometimes',
                'required',
                'max:60',
                Rule::unique('user')->ignore($id, 'user_id,deleted_at,NULL')
            ];
            $fields['password'] = 'sometimes|required|min:8|regex:/[0-9]/|regex:/[a-z]/|regex:/[A-Z]/';

            if (array_key_exists('pseudonymize_at', $request) && $isAdminRequest === true) {
                $fields = $this->validatePseudonymizeData($fields, $request, $id);
            }

            foreach (self::NULLABLE_FIELDS as $nullable) {
                if (array_key_exists($nullable, $request) && !$request[$nullable]) {
                    $fields[$nullable] = 'nullable';
                }
            }
        }

        //If the request didn't came from CI API
        if ($isAdminRequest === false) {
            $fields['first_name'] = 'required|max:60';
            $fields['last_name'] = 'required|max:60';
            $fields['country_id'] = 'required|integer|exists:country,country_id,deleted_at,NULL';
            $fields['profile_text'] = 'nullable';
            $fields['department'] = 'max:60';
            $fields['why_i_volunteer'] = 'nullable';
            $fields['employee_id'] = ['max:60', 'nullable', Rule::unique('user')->ignore($id, 'user_id,deleted_at,NULL')];
            $fields['timezone_id'] = 'required|integer|exists:timezone,timezone_id,deleted_at,NULL';
            $fields['custom_fields.*.field_id'] = 'sometimes|required|exists:user_custom_field,field_id,deleted_at,NULL';
        }

        $validator = Validator::make($request, $fields);
        if ($validator->fails()) {
            return $this->responseHelper->error(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
                config('constants.error_codes.ERROR_USER_INVALID_DATA'),
                $validator->errors()->first()
            );
        }
        return true;
    }

    /**
     * Update the validation rules if data is pseudonymized
     *
     * @param array $fields
     * @param array $request
     * @return array
     */
    private function validatePseudonymizeData($fields, $request, $id)
    {
        $pseudomizeFields = $this->helpers->getSupportedFieldsToPseudonymize();
        $user = $this->findById($id);

        if ($user->pseudonymize_at === '0000-00-00 00:00:00' || $user->pseudonymize_at === null) {
            foreach ($pseudomizeFields as $pseudomize) {
                $rules = ['sometimes', 'required'];

                if ($pseudomize === 'email') {
                    $fields[$pseudomize] = array_push($rules, 'email');
                }

                if ($pseudomize === 'linked_in_url') {
                    $fields[$pseudomize] = array_push($rules, 'valid_linkedin_url');
                }
                $fields[$pseudomize] = implode('|', $rules);
            }
        }
        return $fields;
    }

    /**
     * Unset the pseudonymized fields of the user
     *
     * @param array $data
     * @return array
     */
    public function unsetPseudonymizedFields($data)
    {
        $pseudonymizeFields = $this->helpers->getSupportedFieldsToPseudonymize();
        foreach ($pseudonymizeFields as $field) {
            if (array_key_exists($field, $data)) {
                unset($data[$field]);
            }
        }

        if (array_key_exists('pseudonymize_at', $data)) {
            unset($data['pseudonymize_at']);
        }
        return $data;
    }
}
