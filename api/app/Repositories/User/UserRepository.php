<?php
namespace App\Repositories\User;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\User;
use App\Helpers\Helpers;
use App\Models\UserSkill;
use App\Models\UserCustomFieldValue;
use App\Models\Availability;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\Models\Mission;
use App\Helpers\LanguageHelper;
use App\Repositories\UserCustomField\UserCustomFieldRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\UserDonationGoal;

class UserRepository implements UserInterface
{
    /**
     * @var App\User
     */
    public $user;

    /**
     * @var App\Models\UserSkill
     */
    public $userSkill;

    /**
     * @var App\Models\UserCustomFieldValue
     */
    public $userCustomFieldValue;

    /**
     * @var App\Models\Availability
     */
    public $availability;

    /**
     * @var App\Helpers\Helpers
     */
    private $helpers;

    /**
     * @var App\Repositories\UserCustomField\UserCustomFieldRepository
     */
    private $userCustomFieldRepository;

    /**
     * @var App\Models\Mission
     */
    private $mission;

    /**
     * @var App\Helpers\LanguageHelper
     */
    private $languageHelper;

    /**
     * @var App\Models\UserDonationGoal
     */
    private $userDonationGoal;

    /**
     * Create a new User repository instance.
     *
     * @param  App\User $user
     * @param  App\Models\UserSkill $userSkill
     * @param  App\Models\UserCustomFieldValue $userCustomFieldValue
     * @param  App\Models\Availability $availability
     * @param  App\Helpers\LanguageHelper $languageHelper
     * @param  App\Models\Mission $mission
     * @param  App\Helpers\Helpers $helpers
     * @param  App\Models\UserDonationGoal
     * @return void
     */
    public function __construct(
        User $user,
        UserSkill $userSkill,
        UserCustomFieldValue $userCustomFieldValue,
        Availability $availability,
        LanguageHelper $languageHelper,
        Mission $mission,
        Helpers $helpers,
        UserCustomFieldRepository $userCustomFieldRepository,
        UserDonationGoal $userDonationGoal
    ) {
        $this->user = $user;
        $this->userSkill = $userSkill;
        $this->userCustomFieldValue = $userCustomFieldValue;
        $this->availability = $availability;
        $this->languageHelper = $languageHelper;
        $this->mission = $mission;
        $this->helpers = $helpers;
        $this->userCustomFieldRepository = $userCustomFieldRepository;
        $this->userDonationGoal = $userDonationGoal;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Array $request
     * @return App\User
     */
    public function store(array $request): User
    {
        $user = $this->user->create($request);
        return $user;
    }

    /**
     * Get listing of users
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function userList(Request $request): LengthAwarePaginator
    {
        $activityLogAction = config('constants.activity_log_actions.LOGIN');
        $activityLogType = config('constants.activity_log_types.AUTH');

        /**
        * TODO: optimize query instead of getting first login|last_login column
        * in activity_log, add new column to user first login|last_login
        */
        $user = $this->user;
        $userQuery = $user->selectRaw("
            user.user_id,
            first_name,
            last_name,
            email,
            password,
            case when(avatar = '' || avatar is null)
                then '' else avatar
                end as avatar,
            timezone_id,
            availability_id,
            why_i_volunteer,
            employee_id,
            department,
            position,
            city_id,
            country_id,
            profile_text,
            linked_in_url,
            status,
            language_id,
            title,
            expiry,
            invitation_sent_at,
            pseudonymize_at,
            MIN(activity_log.date) as first_login,
            MAX(activity_log.date) as last_login
        ")->with('city', 'country', 'timezone')
        ->leftJoin('activity_log', function ($join) use ($user, $activityLogAction, $activityLogType) {
            $join->on('user.user_id', '=', 'activity_log.user_id')
                ->where('activity_log.action', $activityLogAction)
                ->where('activity_log.type', $activityLogType);
        });

        if ($request->has('search')) {
            $userQuery->where(function ($query) use ($request) {
                $query->orWhere('first_name', 'like', '%' . $request->input('search') . '%');
                $query->orWhere('last_name', 'like', '%' . $request->input('search') . '%');
                $query->orWhere('email', 'like', '%' . $request->input('search') . '%');
            });
        }

        if ($request->has('email')) {
            $userQuery->where('email', $request->input('email'));
        }

        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $sortBy = 'user_id';

            switch ($request->get('field')) {
                case 'fullName':
                case 'firstName':
                    $sortBy = 'first_name';
                    break;
                case 'email':
                    $sortBy = 'email';
                    break;
                case 'volunteerStatus':
                    $sortBy = 'status';
                    break;
                case 'country':
                    $sortBy = 'country_id';
                    break;
                case 'language':
                    $sortBy = 'language_id';
                    break;
                case 'lastName':
                    $sortBy = 'last_name';
                    break;
                case 'title':
                    $sortBy = 'title';
                    break;
                case 'department':
                    $sortBy = 'department';
                    break;

            }
            $userQuery->orderBy($sortBy, $orderDirection);
        }

        // Needed as we use MIN/MAX(activity_log.date)
        $userQuery->groupBy('user.user_id');

        return $userQuery->paginate($request->perPage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param array $request
     * @param int $id
     * @return User
     */
    public function update(array $request, int $id): User
    {
        $user = $this->user->findOrFail($id);
        $user->update($request);
        return $user;
    }

    /**
     * Find specified resource in storage.
     *
     * @param int $id
     * @return User
     */
    public function find(int $id): User
    {
        return $this->user->findUser($id);
    }

    /**
     * Remove specified resource in storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->user->deleteUser($id);
    }

    /**
     * Store a newly created resource into database
     *
     * @param array $request
     * @param int $id
     * @return array
     */
    public function linkSkill(array $request, int $id): array
    {
        $this->user->findOrFail($id);
        $skillIds = [];
        foreach ($request['skills'] as $value) {
            $skillDetails = $this->userSkill->linkUserSkill($id, $value['skill_id']);
            array_push($skillIds, ['skill_id' => $skillDetails->skill_id]);
        }
        return $skillIds;
    }

    /**
     * Remove the specified resource from storage
     *
     * @param array $request
     * @param int $userId
     * @return array
     */
    public function unlinkSkill(array $request, int $userId): array
    {
        $this->user->findOrFail($userId);
        $unskillIds = [];
        foreach ($request['skills'] as $value) {
            $this->userSkill->deleteUserSkill($userId, $value['skill_id']);
            array_push($unskillIds, ['skill_id' => $value['skill_id']]);
        }
        return $unskillIds;
    }

    /**
     * Display a listing of specified resources.
     *
     * @param int $userId
     * @return Collection
     */
    public function userSkills(int $userId): Collection
    {
        $this->user->findOrFail($userId);
        return $this->userSkill->with('skill')->where('user_id', $userId)->get();
    }

    /**
     * List all the users
     *
     * @param int $userId
     * @return Collection
     */
    public function listUsers(int $userId) : Collection
    {
        return $this->user->where([['user_id', '<>', $userId],['is_profile_complete', '1']])->get();
    }

    /**
     * Search user
     *
     * @param string|null $text
     * @param int $userId
     * @return Collection
     */
    public function searchUsers(string $text = null, int $userId): Collection
    {
        return $this->user->searchUser($text, $userId)->get();
    }

    /**
     * Get user detail by email id
     *
     * @param string $email
     * @return App\User
     */
    public function getUserByEmail(string $email): User
    {
        $user = $this->user->getUserByEmail($email);

        if (is_null($user)) {
            throw new ModelNotFoundException(
                trans('messages.custom_error_message.ERROR_USER_NOT_FOUND')
            );
        }
        return $user;
    }

    /**
     *Add/Update user custom field value.
    *
    * @param array $userCustomFields
    * @param int $userId
    * @return null|App\Models\UserCustomFieldValue
    */
    public function updateCustomFields(array $userCustomFields, int $userId): ?UserCustomFieldValue
    {
        foreach ($userCustomFields as $data) {
            $userCustomFieldData = [
                'field_id' => $data['field_id'],
                'user_id' => $userId,
                'value' => $data['value']
            ];

            $userCustomField = $this->userCustomFieldValue->createOrUpdateCustomFieldValue(
                ['field_id' => $data['field_id'], 'user_id' => $userId],
                $userCustomFieldData
            );
            unset($userCustomFieldData);
        }
        return $userCustomField ?? null;
    }

    /**
     * Find specified resource in storage.
     *
     * @param  int  $id
     * @return App\User
     */
    public function findUserDetail(int $id): User
    {
        return $this->user->findUserDetail($id);
    }

    /**
     * Get Availability.
     *
     * @return Illuminate\Support\Collection
     */
    public function getAvailability(): SupportCollection
    {
        return $this->availability->getAvailability();
    }

    /**
     * Delete skills by userId
     *
     * @param int $userId
     * @return bool
     */
    public function deleteSkills(int $userId): bool
    {
        return $this->userSkill->deleteUserSkills($userId);
    }

    /**
     * Change user's password
     *
     * @param int $id
     * @param string $password
     *
     * @return bool
     */
    public function changePassword(int $id, string $password): bool
    {
        // Fetch user details from system and update password
        $userDetail = $this->user->find($id);
        $userDetail->password = $password;
        return $userDetail->save();
    }

    /**
     * Get user's detail by email
     *
     * @param string $email
     * @return null||App/User
     */
    public function findUserByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    /**
     * Get user goal hours
     *
     * @param int $userId
     * @return null|int
     */
    public function getUserHoursGoal(int $userId): ?int
    {
        return $this->user->getUserHoursGoal($userId);
    }

    /**
     * Update cookie agreement date
     *
     * @param int $userId
     * @return bool
     */
    public function updateCookieAgreement(int $userId): bool
    {
        $now = Carbon::now()->toDateTimeString();

        return $this->user->where('user_id', $userId)->update(['cookie_agreement_date' => $now]);
    }

    /**
     * Get timezone from user id
     *
     * @param int $userId
     * @return string
     */
    public function getUserTimezone(int $userId): string
    {
        return $this->user->with('timezone')->where('user_id', $userId)->first()->timezone['timezone'];
    }

    /**
     * Get specific user timesheet summary
     *
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getTimesheetSummary($request, $userId): Collection
    {
        $publicationStatus = config("constants.publication_status");
        $applicationStatus = config("constants.application_status");

        $timesheet = $this->mission
            ->selectRaw('
                SEC_TO_TIME(
                    SUM(
                        TIME_TO_SEC(
                            IF(mission.mission_type = "TIME", timesheet.time, null)
                        )
                    )
                ) as total_timesheet_time,
                SUM(
                    IF(mission.mission_type = "GOAL", timesheet.action, 0)
                ) as total_timesheet_action,
                COUNT(*) as total_timesheet
            ')
            ->leftjoin('timesheet', 'timesheet.mission_id', '=', 'mission.mission_id')
            ->where('publication_status', $publicationStatus['APPROVED'])
            ->where('timesheet.user_id', $userId)
            ->whereIn('timesheet.status', [
                config("constants.timesheet_status.APPROVED"),
                config("constants.timesheet_status.AUTOMATICALLY_APPROVED"),
            ])
            ->whereHas('missionApplication', function ($query) use ($userId, $applicationStatus) {
                $query->where('user_id', $userId);
                $query->where([
                    'user_id' => $userId,
                    'approval_status' => $applicationStatus["AUTOMATICALLY_APPROVED"]
                ]);
            });

        if ($request->has('day_volunteered') && $request->get('day_volunteered')) {
            $timesheet->where('timesheet.day_volunteered', strtoupper($request->get('day_volunteered')));
        }

        if ($request->has('mission_type') && $request->get('mission_type')) {
            $timesheet->where('mission.mission_type', strtoupper($request->get('mission_type')));
        }

        return $timesheet->get();
    }

    /**
     * Get specific user timesheets per mission
     *
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getMissionTimesheet($request, $userId): Collection
    {
        $publicationStatus = config("constants.publication_status");
        $applicationStatus = config("constants.application_status");

        $language = $this->languageHelper->getLanguageDetails($request);
        $languageId = $language->language_id;

        $timesheet = $this->mission
            ->selectRaw('
                mission.mission_id,
                mission.mission_type,
                mission_language.title as mission_title,
                mission_language.objective as mission_objective,
                SEC_TO_TIME(
                    SUM(
                        TIME_TO_SEC(
                            IF(mission.mission_type = "TIME", timesheet.time, null)
                        )
                    )
                ) as total_timesheet_time,
                SUM(
                    IF(mission.mission_type = "GOAL", timesheet.action, 0)
                ) as total_timesheet_action,
                COUNT(*) as total_timesheet
            ')
            ->leftJoin('mission_language', 'mission_language.mission_id', '=', 'mission.mission_id')
            ->leftJoin('timesheet', 'timesheet.mission_id', '=', 'mission.mission_id')
            ->where('publication_status', $publicationStatus['APPROVED'])
            ->where('mission_language.language_id', $languageId)
            ->where('timesheet.user_id', $userId)
            ->whereIn('timesheet.status', [
                config("constants.timesheet_status.APPROVED"),
                config("constants.timesheet_status.AUTOMATICALLY_APPROVED"),
            ])
            ->whereHas('missionApplication', function ($query) use ($userId, $applicationStatus) {
                $query->where('user_id', $userId);
                $query->where([
                    'user_id' => $userId,
                    'approval_status' => $applicationStatus["AUTOMATICALLY_APPROVED"]
                ]);
            })
            ->groupBy('mission.mission_id');

        if ($request->has('day_volunteered') && $request->get('day_volunteered')) {
            $timesheet->where('timesheet.day_volunteered', strtoupper($request->get('day_volunteered')));
        }

        if ($request->has('mission_type') && $request->get('mission_type')) {
            $timesheet->where('mission.mission_type', strtoupper($request->get('mission_type')));
        }

        return $timesheet->get();
    }

    /**
     * Check profile complete status
     *
     * @param int $userId
     * @param Request $request
     * @return User
     */
    public function checkProfileCompleteStatus(int $userId, Request $request): User
    {
        $profileStatus = true;
        $requiredFieldsArray = config('constants.profile_required_fields');
        $userData = $this->find($userId);
        $dataArray = $userData->toArray();
        foreach ($requiredFieldsArray as $value) {
            if ($dataArray[$value] === null) {
                $profileStatus = false;
            }
        }

        $customFields = $this->userCustomFieldRepository->getUserCustomFields($request);

        if (in_array(1, array_column($customFields->toArray(), 'is_mandatory')) && $request->isMethod('post')) {
            $profileStatus = false;
        }

        $profileComplete = '0';
        if ($profileStatus) {
            $profileComplete = '1';
        }

        $userData->update(["is_profile_complete" => $profileComplete]);
        return $userData;
    }

    /**
     * Get specific user content statistics
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function getStatistics($user, $params = null)
    {
        $userId = $user->user_id;

        return $this->user
            ->select([])
            ->withCount([
                'messages',
                'comments',
                'stories' => function (Builder $query) {
                    $query->where('status', config("constants.story_status.PUBLISHED"));
                },
                'stories as stories_views_count' => function (Builder $query) use ($userId) {
                    $query->join('story_visitor as sv', 'sv.story_id', '=', 'story.story_id')
                        ->where('story.status', config("constants.story_status.PUBLISHED"));
                },
                'storyInvites as stories_invited_users_count'
            ])
            ->where('user.user_id', $userId)
            ->get();
    }

    /**
     * Get user's volunteer summary
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function volunteerSummary($user, $params = null)
    {
        $activityLogAction = config('constants.activity_log_actions.LOGIN');
        $activityLogType = config('constants.activity_log_types.AUTH');

        return $user
            ->selectRaw("
                MAX(timesheet.date_volunteered) as last_volunteer,
                MAX(activity_log.date) as last_login
            ")
            ->leftJoin('activity_log', function ($join) use ($user, $activityLogAction, $activityLogType) {
                $join->on('user.user_id', '=', 'activity_log.user_id')
                    ->where('activity_log.action', $activityLogAction)
                    ->where('activity_log.type', $activityLogType)
                    ->where('activity_log.user_id', '=', $user->user_id);
            })
            ->leftJoin('timesheet', function ($join) use ($user) {
                $join->on('user.user_id', '=', 'timesheet.user_id')
                    ->where('timesheet.deleted_at', '=', null)
                    ->where('timesheet.user_id', '=', $user->user_id);
            })
            ->get();
    }

    /**
     * Get user's missions
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function getMissionCount($user, $params = null)
    {
        $pendingStatus = config('constants.application_status.PENDING');
        $approveStatus = config('constants.application_status.AUTOMATICALLY_APPROVED');

        return $user
            ->selectRaw("
                SUM(IF(mission_application.approval_status = ?, 1, 0)) as open_volunteer_request,
                SUM(IF(mission_application.approval_status = ?, 1, 0)) as mission
            ", [$pendingStatus, $approveStatus])
            ->join('mission_application', 'user.user_id', '=', 'mission_application.user_id')
            ->where('mission_application.user_id', '=', $user->user_id)
            ->where('mission_application.deleted_at', '=', null)
            ->get();
    }

    /**
     * Get user's favorite mission
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function getFavoriteMission($user, $params = null)
    {
        return $user
            ->FavouriteMission()
            ->selectRaw('COUNT(favorite_mission.favourite_mission_id) as favourite_mission')
            ->get();
    }

    /**
     * Get specific user organization ccunt
     *
     * @param App\User $user
     * @param Array $params all get parameteres
     *
     * @return Array
     */
    public function getOrgCount($user, $params = null)
    {
        return $user
            ->missionApplication()
            ->selectRaw('
                COUNT(DISTINCT mission.organization_id) as organization_count
            ')
            ->join('mission', 'mission.mission_id', '=', 'mission_application.mission_id')
            ->where('mission_application.approval_status', '<>', config('constants.application_status.REFUSED'))
            ->get();
    }

    private function getUserArrayDataFromRequest(Request $request)
    {
        $requestData = $request->toArray();

        if (array_key_exists('backend_internal_notes', $requestData)) {
            unset($requestData['backend_internal_notes']);
        }

        $requestData['expiry'] = (isset($request->expiry)) && $request->expiry
            ? $request->expiry : null;
        if (isset($request->status)) {
            $requestData['status'] = $request->status
                ? config('constants.user_statuses.ACTIVE')
                : config('constants.user_statuses.INACTIVE');
        }

        return $requestData;
    }

    /**
     * Update user donation goal
     *
     * @param Request $request
     */
    public function updateDonationGoal(Request $request)
    {
        $userId = $request->auth->user_id;
        $this->userDonationGoal->updateOrCreate(
            [
                'user_id' => $userId,
                'donation_goal_year' => $request->get('donation_goal_year')
            ],
            [
                'donation_goal' => $request->get('donation_goal'),
                'donation_goal_year' => $request->get('donation_goal_year'),
                'user_id' => $userId
            ]
        );
    }

    /**
     * Get user donation goal
     *
     * @param int $userId
     */
    public function getUserDonationGoal(int $userId)
    {
        return $this->userDonationGoal->where('user_id', $userId)->get();
    }
    
    /**
     * @param  bool
     *
     * @return  int
     */
    public function getUserCount(
        bool $includeInactive = false,
        bool $includeAdmin = false
    ): int {
        $query = $this->user->selectRaw('COUNT(1) AS user_count');

        if (!$includeAdmin) {
            $query->where('is_admin', '<>', 1);
        }

        $status = [
            config('constants.user_statuses.ACTIVE'),
        ];

        if ($includeInactive) {
            $status[] = config('constants.user_statuses.INACTIVE');
        }

        $query->whereIn('status', $status);

        return $query->first()->user_count;
    }
}
