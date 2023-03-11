<?php

namespace App\Helpers;

use App\Exceptions\TenantDomainNotFoundException;
use App\Traits\RestExceptionHandlerTrait;
use Bschmitt\Amqp\Amqp;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use stdClass;
use Throwable;

class Helpers
{
    use RestExceptionHandlerTrait;

    /**
     * @var DB
     */
    private $db;

    /**
     * Amqp
     *
     * @var Amqp
     */
    private $amqp;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(Amqp $amqp)
    {
        $this->db = app()->make('db');
        $this->amqp = $amqp;
    }

    /**
     * It will return tenant name from request
     * @param Illuminate\Http\Request $request
     * @return string
     */
    public function getSubDomainFromRequest(Request $request): string
    {
        if ($request->header('php-auth-pw') && $request->header('php-auth-user')) {
            return $this->getDomainFromUserAPIKeys($request);
        } elseif (!empty($request->query('tenant'))) {
            return $this->getTenantDomainByTenantId($request->query('tenant'));
        } elseif (in_array(env('APP_ENV'), ['local', 'testing'])) {
            return env('DEFAULT_TENANT');
        } else {
            return parse_url($request->headers->all()['referer'][0])['host'];
        }
    }

    /**
     * It will retrieve tenant id and sponsor id from tenant table
     *
     * @param Request $request
     * @return object $tenant
     */
    public function getTenantIdAndSponsorIdFromRequest(Request $request): object
    {
        $domain = $this->getSubDomainFromRequest($request);

        $this->switchDatabaseConnection('mysql');

        $tenantIdAndSponsorId = $this->db->table('tenant')
            ->select('tenant_id', 'sponsor_id')
            ->where('name', $domain)
            ->whereNull('deleted_at')
            ->first();

        $this->switchDatabaseConnection('tenant');

        return $tenantIdAndSponsorId;
    }

    /**
     * Get base URL from request object
     *
     * @param Illuminate\Http\Request $request
     * @return mixed
     */
    public function getRefererFromRequest(Request $request)
    {
        if (isset($request->headers->all()['referer'])) {
            $parseUrl = parse_url($request->headers->all()['referer'][0]);
            return $parseUrl['scheme'] . '://' . $parseUrl['host'] . env('APP_PATH');
        } else {
            return env('APP_MAIL_BASE_URL');
        }
    }

    /**
     * It will retrive tenant details from tenant table
     *
     * @param Illuminate\Http\Request $request
     * @return object $tenant
     */
    public function getTenantDetail(Request $request): object
    {
        // Connect master database to get language details
        $tenantName = $this->getSubDomainFromRequest($request);
        $this->switchDatabaseConnection('mysql');
        $tenant = $this->db->table('tenant')->where('name', $tenantName)->whereNull('deleted_at')->first();
        // Connect tenant database
        $this->switchDatabaseConnection('tenant');

        return $tenant;
    }

    /**
     * Switch database connection runtime
     *
     * @param string $connection
     * @return void
     * @throws Exception
     */
    public function switchDatabaseConnection(string $connection)
    {
        // Set master connection
        $pdo = $this->db->connection('mysql')->getPdo();
        $pdo->exec('SET NAMES utf8mb4');
        $pdo->exec('SET CHARACTER SET utf8mb4');
        Config::set('database.default', 'mysql');

        if ($connection == "tenant") {
            $pdo = $this->db->connection('tenant')->getPdo();
            $pdo->exec('SET NAMES utf8mb4');
            $pdo->exec('SET CHARACTER SET utf8mb4');
            Config::set('database.default', 'tenant');
        }
    }

    /**
     * Create database connection runtime
     *
     * @param int $tenantId
     */
    public function createConnection(int $tenantId)
    {
        $this->db->purge('tenant');
        Config::set('database.connections.tenant', array(
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => 'ci_tenant_' . $tenantId,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ));
        // Create connection for the tenant database
        $pdo = $this->db->connection('tenant')->getPdo();
        $pdo->exec('SET NAMES utf8mb4');
        $pdo->exec('SET CHARACTER SET utf8mb4');
        // Set default database
        Config::set('database.default', 'tenant');
    }

    /**
     * Get date according to user timezone
     *
     * @param string $date
     * @return string
     */
    public function getUserTimeZoneDate(string $date): string
    {
        if (!($date instanceof Carbon)) {
            $date = Carbon::parse($date);
        }
        return $date->setTimezone(config('constants.TIMEZONE'))->format(config('constants.DB_DATE_TIME_FORMAT'));
    }

    /**
     * Get JWT token
     *
     * @param int $userId
     * @param string $tenantName
     * @return string
     */
    public static function getJwtToken(
        int $userId,
        string $tenantName,
        bool $isSSO = false,
        int $duration = 14400
    ) : string {
        $payload = [
            'sub' => $userId, // Subject of the token
            'fqdn' => $tenantName
        ];

        if ($isSSO) {
            $payload['sso'] = true;
        }

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return Helpers::encodeJwtToken($payload, $duration);
    }

    public static function encodeJwtToken(
        $params = [],
        $duration = 300
    ) {
        $payload = array_merge([
            'iss' => "lumen-jwt",
            'iat' => time(),
            'exp' => time() + $duration,
        ], $params);
        return JWT::encode($payload, env('JWT_SECRET'));
    }

    public static function decodeJwtToken($token)
    {
        return JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    }

    /**
     * Get tenant default profile image for user
     *
     * @param string $tenantName
     * @return string
     */
    public function getUserDefaultProfileImage(string $tenantName): string
    {
        $assetsFolder = env('AWS_S3_ASSETS_FOLDER_NAME');
        $imagesFolder = env('AWS_S3_IMAGES_FOLDER_NAME');
        $defaultProfileImage = config('constants.AWS_S3_DEFAULT_PROFILE_IMAGE');

        return S3Helper::makeTenantS3BaseUrl($tenantName)
            . $assetsFolder
            . '/'
            . $imagesFolder
            . '/'
            . $defaultProfileImage;
    }

    /**
     * Get tenant details from tenant name only
     *
     * @param string $tenantName
     * @return stdClass $tenant
     */
    public function getTenantDetailsFromName(string $tenantName): stdClass
    {
        // Get tenant details based on tenant name
        $tenant = $this->db->table('tenant')->where('name', $tenantName)->first();
        if (is_null($tenant)) {
            throw new TenantDomainNotFoundException(
                trans('messages.custom_error_message.ERROR_TENANT_DOMAIN_NOT_FOUND'),
                config('constants.error_codes.ERROR_TENANT_DOMAIN_NOT_FOUND')
            );
        }
        // Create database connection based on tenant id
        $this->createConnection($tenant->tenant_id);
        $pdo = $this->db->connection('tenant')->getPdo();
        Config::set('database.default', 'tenant');

        return $tenant;
    }

    /**
     * Get fetch all tenant settings detais
     *
     * @param \Illuminate\Http\Request $request
     * @return mix
     */
    public function getAllTenantSetting(Request $request)
    {
        $tenant = $this->getTenantDetail($request);
        // Connect master database to get tenant settings
        $this->switchDatabaseConnection('mysql');

        $keys = $request->keys ?? [];
        $tenantSetting = $this->db->table('tenant_has_setting')
            ->select(
                'tenant_has_setting.tenant_setting_id',
                'tenant_setting.key',
                'tenant_setting.tenant_setting_id',
                'tenant_setting.description',
                'tenant_setting.title'
            )
            ->leftJoin(
                'tenant_setting',
                'tenant_setting.tenant_setting_id',
                '=',
                'tenant_has_setting.tenant_setting_id'
            )
            ->when(!empty($keys), function ($query) use ($keys) {
                return $query->whereIn('tenant_setting.key', $keys);
            })
            ->whereNull('tenant_has_setting.deleted_at')
            ->whereNull('tenant_setting.deleted_at')
            ->where('tenant_id', $tenant->tenant_id)
            ->orderBy('tenant_has_setting.tenant_setting_id')
            ->get();

        // Connect tenant database
        $this->switchDatabaseConnection('tenant');

        return $tenantSetting;
    }

    /**
     * Get domain from user API key
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getDomainFromUserAPIKeys(Request $request): string
    {
        // Check basic auth passed or not
        $this->switchDatabaseConnection('mysql');
        // authenticate api user based on basic auth parameters
        $apiUser = $this->db->table('api_user')
            ->leftJoin('tenant', 'tenant.tenant_id', '=', 'api_user.tenant_id')
            ->where('api_key', base64_encode($request->header('php-auth-user')))
            ->where('api_user.status', '1')
            ->where('tenant.status', '1')
            ->whereNull('api_user.deleted_at')
            ->whereNull('tenant.deleted_at')
            ->first();

        $this->switchDatabaseConnection('tenant');
        return $apiUser->name;
    }

    /**
     * Change date format
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
    public function changeDateFormat(string $date, string $dateFormat): string
    {
        return date($dateFormat, strtotime($date));
    }

    /**
     * Convert in report time format
     *
     * @param string $totalHours
     * @return string
     */
    public function convertInReportTimeFormat(string $totalHours): string
    {
        $convertedHours = (int)($totalHours / 60);
        $hours = $convertedHours . "h";
        $minutes = $totalHours % 60;
        $minutes = sprintf("%02d", $minutes);
        return $hours . $minutes;
    }

    /**
     * Convert in report hours format
     *
     * @param string $totalHours
     * @return string
     */
    public function convertInReportHoursFormat(string $totalHours): string
    {
        $hours = (int)($totalHours / 60);
        $minutes = ($totalHours % 60) / 60;
        $totalHours = $hours + $minutes;
        return number_format((float)$totalHours, 2, '.', '');
    }

    /**
     * Trim text after x words
     *
     * @param string $phrase
     * @param int maxWords
     * @return null|string
     */
    public function trimText(string $phrase, int $maxWords)
    {
        $phrase_array = explode(' ', $phrase);
        if (count($phrase_array) > $maxWords && $maxWords > 0) {
            $phrase = implode(' ', array_slice($phrase_array, 0, $maxWords)) . '...';
        }
        return $phrase;
    }

    /**
     * Get tenant default assets url
     *
     * @param string $tenantName
     * @return string
     */
    public function getAssetsUrl(string $tenantName): string
    {
        return S3Helper::makeTenantS3BaseUrl($tenantName)
            . env('AWS_S3_ASSETS_FOLDER_NAME')
            .  '/'
            . env('AWS_S3_IMAGES_FOLDER_NAME') . '/';
    }

    /**
     * Get language details
     * @param int $languageId
     * @return Object
     */
    public function getLanguageDetail(int $languageId): ?Object
    {
        $this->switchDatabaseConnection('mysql');
        $language = $this->db->table('language')->where('language_id', $languageId)->whereNull('deleted_at')->first();
        $this->switchDatabaseConnection('tenant');
        return $language;
    }

    /**
     * Remove unwanted characters from json
     * @param string $filePath
     * @return string
     */
    public function removeUnwantedCharacters(string $filePath): string
    {
        $jsonFileContent = file_get_contents($filePath);

        // This will remove unwanted characters.
        for ($i = 0; $i <= 31; ++$i) {
            $jsonFileContent = str_replace(chr($i), "", $jsonFileContent);
        }
        $jsonFileContent = str_replace(chr(127), "", $jsonFileContent);

        // This is the most common part
        // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
        // here we detect it and we remove it, basically it's the first 3 characters
        if (0 === strpos(bin2hex($jsonFileContent), 'efbbbf')) {
            $jsonFileContent = substr($jsonFileContent, 3);
        }

        return $jsonFileContent;
    }

    /**
     * Sync volunteer to Optimyapp
     *
     * @param Request $request
     * @param User $user
     *
     * @return boolean
     */
    public function syncUserData($request, $user)
    {
        if ($user->pseudonymize_at  && $user->pseudonymize_at !== '0000-00-00 00:00:00') {
            return false;
        }

        $tenantIdAndSponsorId = $this->getTenantIdAndSponsorIdFromRequest($request);

        $params = [
            'activity_type' => 'user',
            'sponsor_frontend_id' => $tenantIdAndSponsorId->sponsor_id,
            'ci_user_id' => $user->user_id,
            'tenant_id' => $tenantIdAndSponsorId->tenant_id
        ];

        $payload = json_encode($params);

        $this->amqp->publish(
            'ciSynchronizer',
            $payload,
            [
                'queue' => 'ciSynchronizer'
            ]
        );

        return true;
    }

    /**
     * Retrieve tenant's name
     *
     * @param Request
     * @return String
     */
    public function getTenantDomainByTenantId($tenantId): String
    {
        $connection = Config::get('database.default');
        $this->switchDatabaseConnection('mysql');

        $tenant = $this->db->table('tenant')
            ->select('name')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->first();

        $this->switchDatabaseConnection($connection);

        return $tenant->name;
    }

    /**
     * Retrieve tenant's basic auth
     *
     * @return String
     */
    public static function getBasicAuth()
    {
        return 'Basic '.base64_encode(env('API_KEY').':'.env('API_SECRET'));
    }

    /** Check if email address is an admin user.
     *
     * @param String
     * @return Boolean
     */
    public function isAdminUser($email): Bool
    {
        $connection = Config::get('database.default');
        $this->switchDatabaseConnection('mysql');

        $adminUser = $this->db->table('admin_user')
            ->select('id')
            ->where('email', $email)
            ->where('role', 'optimy_admin')
            ->whereNull('deleted_at')
            ->first();

        $this->switchDatabaseConnection($connection);

        return (bool)$adminUser;
    }

    /**
     * Check for valid currency from `ci_admin` table.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $currencyCode
     * @return bool
     */
    public function isValidTenantCurrency(Request $request, string $currencyCode)
    {
        $tenant = $this->getTenantDetail($request);
        // Connect master database to get currency details
        $this->switchDatabaseConnection('mysql');

        $tenantCurrency = $this->db->table('tenant_currency')
            ->where('tenant_id', $tenant->tenant_id)
            ->where('code', $currencyCode)
            ->where('is_active', '1');

        // Connect tenant databases
        $this->switchDatabaseConnection('tenant');

        return ($tenantCurrency->count() > 0) ? true : false;
    }

    /**
     * Get tenant activated currencies
     *
     * @param Request $request
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getTenantActivatedCurrencies(Request $request) : Collection
    {
        $tenant = $this->getTenantDetail($request);

        // Connect master database to get tenant currency
        $this->switchDatabaseConnection('mysql');

        $tenantCurrencies = $this->db->table('tenant_currency')
            ->select(
                'tenant_currency.code',
                'tenant_currency.default'
            )
            ->where('tenant_id', $tenant->tenant_id)
            ->where('tenant_currency.is_active', '1')
            ->orderBy('tenant_currency.code', 'ASC')
            ->get();

        // Connect tenant database
        $this->switchDatabaseConnection('tenant');

        return $tenantCurrencies;
    }

    public function getSupportedFieldsToPseudonymize()
    {
        return [
            'first_name',
            'last_name',
            'email',
            'employee_id',
            'linked_in_url',
            'position',
            'department',
            'profile_text',
            'why_i_volunteer'
        ];
    }
}
