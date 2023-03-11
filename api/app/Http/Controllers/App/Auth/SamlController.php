<?php
namespace App\Http\Controllers\App\Auth;

use App\Exceptions\MaximumUsersReachedException;
use App\Helpers\Helpers;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\TenantOption;
use App\Repositories\City\CityRepository;
use App\Repositories\Country\CountryRepository;
use App\Repositories\TenantOption\TenantOptionRepository;
use App\Repositories\Timezone\TimezoneRepository;
use App\Services\UserService;
use App\Exceptions\SamlException;
use App\Traits\RestExceptionHandlerTrait;
use App\User;
use Bschmitt\Amqp\Amqp;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;

class SamlController extends Controller
{
    private $helpers;
    private $userService;
    private $tenantOptionRepository;
    private $languageHelper;
    private $timezoneRepository;
    private $countryRepository;
    private $cityRepository;
    private $availability;

    public function __construct(
        Helpers $helpers,
        ResponseHelper $responseHelper,
        UserService $userService,
        TenantOptionRepository $tenantOptionRepository,
        LanguageHelper $languageHelper,
        TimezoneRepository $timezoneRepository,
        CountryRepository $countryRepository,
        CityRepository $cityRepository,
        Availability $availability
    ) {
        $this->helpers = $helpers;
        $this->responseHelper = $responseHelper;
        $this->userService = $userService;
        $this->tenantOptionRepository = $tenantOptionRepository;
        $this->languageHelper = $languageHelper;
        $this->timezoneRepository = $timezoneRepository;
        $this->countryRepository = $countryRepository;
        $this->cityRepository = $cityRepository;
        $this->availability = $availability;
    }

    public function sso(Request $request)
    {
        $settings = $this->getIdentityProviderSettings();
        if (!isset($settings['idp_id'])) {
            SamlException::throw('ERROR_INVALID_SAML_IDENTITY_PROVIDER');
        } elseif ($settings['idp_id'] !== $request->query('t')) {
            SamlException::throw('ERROR_INVALID_SAML_ACCESS');
        }

        $auth = new Auth($this->getSamlSettings($settings, $request->query('tenant')));

        return $auth->login();
    }

    public function acs(Request $request, User $user)
    {
        $validationErrors = [];
        $settings = $this->getIdentityProviderSettings();
        if (!isset($settings['idp_id'])) {
            SamlException::throw('ERROR_INVALID_SAML_IDENTITY_PROVIDER');
        } elseif ($settings['idp_id'] !== $request->query('t')) {
            SamlException::throw('ERROR_INVALID_SAML_ACCESS');
        }

        $auth = new Auth($this->getSamlSettings($settings, $request->query('tenant')));
        $authRedirectBaseUrl = implode('', [
            $request->secure() ? 'https://' : 'http://',
            $settings['frontend_fqdn'],
        ]);
        $auth->processResponse();
        if (!$auth->isAuthenticated()) {
            $errors = $auth->getErrors();
            die('200-Not authenticated. ' . implode('; ', $errors).' - '.$auth->getLastErrorReason());
            $auth->redirectTo($authRedirectBaseUrl);
        }

        $authRedirectAuthSsoUrl = "{$authRedirectBaseUrl}/auth/sso";

        $attributes = [];
        $userData = [];

        $optimyAppMapping = [
            'availability' => 'availability_id',
            'timezone' => 'timezone_id',
            'language' => 'language_id',
            'postal_city' => 'city_id',
            'postal_country' => 'country_id',
            'profile' => 'profile_text',
            'department' => 'department',
            'linkedin' => 'linked_in_url',
            'volunteer' => 'why_i_volunteer',
            'position' => 'position',
            'title' => 'title',
            'expires' => 'expiry',
        ];

        $validProperties = [
            'first_name',
            'last_name',
            'email',
            'availability_id',
            'timezone_id',
            'language_id',
            'city_id',
            'country_id',
            'profile_text',
            'employee_id',
            'department',
            'linked_in_url',
            'why_i_volunteer',
            'title',
            'position',
            'expiry',
        ];

        foreach ($auth->getAttributes() as $key => $attribute) {
            if (empty($attribute[0])) {
                continue;
            }
            $attributes[$key] = count($attribute) > 1 ?
                $attribute :
                $attribute[0];
        }

        foreach ($settings['mappings'] as $mapping) {
            $name = $mapping['name'];

            if (!isset($attributes[$mapping['value']])) {
                continue;
            }

            if (!in_array($name, $validProperties)
                && array_key_exists($name, $optimyAppMapping)
            ) {
                $name = $optimyAppMapping[$name];
            }

            if (!in_array($name, $validProperties)) {
                continue;
            }

            $value = $attributes[$mapping['value']];

            if ($name === 'country_id') {
                $country = $this->countryRepository->getCountryByCode($value);
                if (!$country) {
                    $country = $this->countryRepository->searchCountry($value);
                }
                $value = $country ? $country->country_id : null;
            };

            $userData[$name] = $value;
        }

        if ($validationErrors) {
            $auth->redirectTo(
                "{$authRedirectAuthSsoUrl}/error",
                ['errors' => implode(',', $validationErrors), 'source' => 'saml']
            );
        }

        if (isset($userData['language_id'])) {
            $language = $this->languageHelper->getTenantLanguageByCode($request, $userData['language_id']);
            $userData['language_id'] = $language->language_id;
        }

        if (isset($userData['timezone_id'])) {
            // $timezoneCode = $userData['timezone_id'] ?? 'Europe/Paris'; //env('DEFAULT_TIMEZONE');
            $timezone = $this->timezoneRepository->getTenantTimezoneByCode(
                $userData['timezone_id']
            );
            $userData['timezone_id'] = $timezone->timezone_id;
        }

        if (isset($userData['city_id']) ) {
            $city = $this->cityRepository->searchCity(
                $userData['city_id'],
                $userData['language_id'] ?? null,
                $userData['country_id'] ?? null
            );
            unset($userData['city_id']);
            if ($city) {
                $userData['city_id'] = $city->city_id;
            }
        }

        if (isset($userData['availability_id'])) {
            $availabilityId = $userData['availability_id'];
            unset($userData['availability_id']);
            $availabilityList = $this->availability->getAvailability();
            foreach($availabilityList as $availability) {
                $index = array_search(
                    $availabilityId,
                    array_column($availability['translations'], 'title')
                );

                if ($index === false) {
                    continue;
                }

                $userData['availability_id'] = $availability->availability_id;
                break;
            }
        }

        $email = $auth->getNameId();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $userData['email'] ?? '';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = 'Email';
            $auth->redirectTo(
                "{$authRedirectAuthSsoUrl}/error",
                ['errors' => implode(',', $validationErrors), 'source' => 'saml']
            );
        }

        $userDetail = $user->where('email', $email)->first();
        $userData['email'] = $email;

        $isNewUser = $userDetail === null;

        // Default user's timezone to config default timezone
        //  - if an existing user has not yet set his/her timezone configuration.
        //  - if a new user did not provided a timezone.
        if ((!$isNewUser && !isset($userData['timezone_id']) && !$userDetail->timezone_id)
            || ($isNewUser && !isset($userData['timezone_id']))
        ) {
            // env('DEFAULT_TIMEZONE')
            $timezone = $this->timezoneRepository->getTenantTimezoneByCode(
                env('DEFAULT_TIMEZONE', 'Europe/Paris')
            );
            $userData['timezone_id'] = $timezone->timezone_id;
        }

        // Default user's lanugage to tenant's default language
        //  - if an existing user has not yet set his/her language configuration.
        //  - if a new user did not provided a language code.
        if ((!$isNewUser && !isset($userData['language_id']) && !$userDetail->lanugage_id)
            || ($isNewUser && !isset($userData['language_id']))
        ) {
            $language = $this->languageHelper->getDefaultTenantLanguage($request);
            $userData['language_id'] = $language->language_id;
        }

        try {
            $userDetail = $isNewUser ?
                $this->userService->store($userData) :
                $this->userService->update($userData, $userDetail->user_id);
        } catch (MaximumUsersReachedException $e) {
            $auth->redirectTo(
                "{$authRedirectAuthSsoUrl}/error",
                [
                    'error' => trans('messages.custom_error_message.ERROR_MAXIMUM_USERS_REACHED'),
                    'source' => 'saml'
                ]
            );
        }

        $this->helpers->syncUserData($request, $userDetail);

        if (!$isNewUser && $userDetail->status !== config('constants.user_statuses.ACTIVE')) {
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
                $auth->redirectTo(
                    "{$authRedirectAuthSsoUrl}/error",
                    [
                        'error' => trans('messages.custom_error_message.ERROR_USER_EXPIRED'),
                        'source' => 'saml',
                        'action' => 'login',
                    ]
                );
            }
        }

        $tenantName = $this->helpers->getSubDomainFromRequest($request);

        $token = $this->helpers->getJwtToken(
            $userDetail->user_id,
            $tenantName,
            true,
            60
        );

        $auth->redirectTo(
            $authRedirectAuthSsoUrl,
            ['token' => $token]
        );
    }

    public function slo(Request $request)
    {
        $settings = $this->getIdentityProviderSettings();
        if ($settings['idp_id'] !== $request->query('t')) {
            throw new SamlException(
                trans('messages.custom_error_message.ERROR_INVALID_SAML_IDENTITY_PROVIDER'),
                config('constants.error_codes.ERROR_INVALID_SAML_IDENTITY_PROVIDER')
            );
        }

        $auth = new Auth($this->getSamlSettings($settings, $request->query('tenant')));
        $auth->logout(null, [], null, null, true);

        $auth->redirectTo(
            'http'.($request->secure() ? 's' : '').'://'.$settings['frontend_fqdn'].'/auth/slo'
        );
    }

    public function metadata(Request $request, Response $response)
    {
        $settings = $this->getIdentityProviderSettings();
        if ($settings['idp_id'] !== $request->query('t')) {
            throw new SamlException(
                trans('messages.custom_error_message.ERROR_INVALID_SAML_IDENTITY_PROVIDER'),
                config('constants.error_codes.ERROR_INVALID_SAML_IDENTITY_PROVIDER')
            );
        }

        $samlSettings = new Settings($this->getSamlSettings($settings, $request->query('tenant')));
        $metadata = $samlSettings->getSPMetadata();
        $errors = $samlSettings->validateMetadata($metadata);
        return $response->header('Content-Type', 'text/xml')
            ->setContent($metadata);
    }

    private function getSamlSettings(array $settings, $tenantId)
    {
        return [
            'debug' => env('APP_DEBUG'),
            'strict' => $settings['strict'],
            'security' => $settings['security'],
            'idp' => $settings['idp'],
            'sp' => [
                'entityId' => route('saml.metadata', ['t' => $settings['idp_id'], 'tenant' => $tenantId]),
                'singleSignOnService' => [
                    'url' => route('saml.sso', ['t' => $settings['idp_id'], 'tenant' => $tenantId])
                ],
                'singleLogoutService' => [
                    'url' => route('saml.slo', ['t' => $settings['idp_id'], 'tenant' => $tenantId])
                ],
                'assertionConsumerService' => [
                    'url' => route('saml.acs', ['t' => $settings['idp_id'], 'tenant' => $tenantId])
                ],
                'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'x509cert' => Storage::disk('local')->get('samlCertificate/optimy.cer'),
                'privateKey' => Storage::disk('local')->get('samlCertificate/optimy.pem'),
            ]
        ];
    }

    private function getIdentityProviderSettings()
    {
        $optionSetting = $this->tenantOptionRepository
            ->getOptionValueFromOptionName(TenantOption::SAML_SETTINGS);

        return $optionSetting->getOptionValueAttribute(
            $optionSetting->option_value
        );
    }
}
