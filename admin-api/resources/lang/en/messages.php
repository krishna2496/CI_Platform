<?php
return [

    /**
    * Success messages
    */
    'success' => [
        'MESSAGE_TENANT_CREATED' => 'Tenant created successfully',
        'MESSAGE_TENANT_UPDATED' => 'Tenant details updated successfully',
        'MESSAGE_TENANT_DELETED' => 'Tenant deleted successfully',
        'MESSAGE_TENANT_LISTING' => 'Tenants listed successfully',
        'MESSAGE_NO_RECORD_FOUND' => 'No records found',
        'MESSAGE_TENANT_FOUND' => 'Tenant found successfully',
        'MESSAGE_TENANT_API_USER_LISTING' => 'Tenant\'s API users listed successfully',
        'MESSAGE_API_USER_FOUND' => 'API user found successfully',
        'MESSAGE_API_USER_CREATED_SUCCESSFULLY' => 'API user created successfully',
        'MESSAGE_API_USER_DELETED' => 'API user deleted successfully',
        'MESSAGE_API_USER_UPDATED_SUCCESSFULLY' => 'API user\'s secret key updated successfully',
        'MESSAGE_TENANT_SETTING_LISTING' => 'Tenant setting listed successfully',
        'MESSAGE_TENANT_SETTINGS_UPDATED' => 'Tenant settings updated successfully',
        'MESSAGE_ALL_SETTING_LISTING' => 'All settings listed successfully',
        'MESSAGE_LANGUAGE_FOUND' => 'Language found successfully',
        'MESSAGE_LANGUAGE_LISTING' => 'Languages listed successfully',
        'MESSAGE_LANGUAGE_CREATED' => 'Language added successfully',
        'MESSAGE_LANGUAGE_UPDATED' => 'Language details updated successfully',
        'MESSAGE_NEWS_DELETED' => 'Language deleted successfully',
        'MESSAGE_TENANT_LANGUAGE_ADDED' => 'Tenant language added successfully',
        'MESSAGE_TENANT_LANGUAGE_UPDATED' => 'Tenant language updated successfully',
        'MESSAGE_TENANT_LANGUAGE_LISTING' => 'Tenant languages listed successfully',
        'MESSAGE_TENANT_LANGUAGE_DELETED' => 'Tenant language deleted successfully',
        'MESSAGE_NO_ACTIVITY_LOGS_ENTRIES_FOUND' => 'No activity logs found',
        'MESSAGE_ACTIVITY_LOGS_ENTRIES_LISTING' => 'Activity logs listed successfully',
        'MESSAGE_MIGRATION_CHANGES_APPLIED_SUCCESSFULLY' => 'Migration changes applied successfully on tenant DB.',
        'MESSAGE_SEEDER_CHANGES_APPLIED_SUCCESSFULLY' => 'Migration changes applied successfully on tenant DB.',
        'MESSAGE_TENANT_BACKGROUND_PROCESS_COMPLETED' => 'Tenant background process completed successfully',
        'MESSAGE_TENANT_CURRENCY_ADDED' => 'Tenant currency added successfully',
        'MESSAGE_TENANT_CURRENCY_LISTING' => 'Tenant currency listed successfully',
        'MESSAGE_TENANT_CURRENCY_UPDATED' => 'Tenant currency updated successfully'
    ],

    /**
    * API Error Codes and Message
    */
    'custom_error_message' => [
        'ERROR_TENANT_REQUIRED_FIELDS_EMPTY' => 'Tenant name or sponsored field is empty',
        'ERROR_TENANT_ALREADY_EXIST' => 'Tenant name is already taken, Please try with different name.',
        'ERROR_TENANT_NOT_FOUND' => 'Tenant not found in the system',
        'ERROR_DATABASE_OPERATIONAL' => 'Database operational error',
        'ERROR_NO_DATA_FOUND' => 'No data found',
        'ERROR_INVALID_ARGUMENT' => 'Invalid argument',
        'ERROR_API_USER_NOT_FOUND' => 'API user not found',
        'ERROR_INVALID_JSON' => 'Invalid JSON format',
        'ERROR_LANGUAGE_NOT_FOUND' => 'Language not found in the system',
        'ERROR_TENANT_LANGUAGE_NOT_FOUND' => 'Tenant language not found in the system',
        'ERROR_TENANT_DEFAULT_LANGUAGE_REQUIRED' => 'At least one default language is required',
        'ERROR_INVALID_MIGRATION_FILE_EXTENSION' => 'Invalid file extension',
        'ERROR_DELETE_DEFAULT_TENANT_LANGUAGE' => 'You can not delete default tenant language',
        'ERROR_INVALID_FQDN_NAME' => 'Invalid tenant name',
        'ERROR_LANGUAGE_UNABLE_TO_DELETE' => 'Language can not be deleted as it is currently in use.',
        'ERROR_CURRENCY_CODE_NOT_AVAILABLE' => 'Currency code is not supported.',
        'ERROR_CURRENCY_CODE_NOT_FOUND' => 'Currency not found in system',
        'ERROR_TENANT_CURRENCY_EMPTY_LIST' => 'Tenant has no currency.',
        'CURRENCY_CODE' => 'Currency Code ',
        'CURRENCY_IS_NOT_VALID_ONLY' => ' is not valid',
        'ERROR_IS_ACTIVE_FIELD_MUST_BE_TRUE' => 'Currency should be active to set it as default.',
        'ERROR_DEFAULT_CURRENCY_SHOULD_BE_ACTIVE' => 'Cannot deactivate the default currency.',
        'ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE' => 'Volunteering time or volunteering goal should be active while volunteering setting is enabled.',
        'ERROR_VOLUNTEERING_SHOULD_BE_ENABLED' => 'Volunteering setting should be enabled to update volunteering time or volunteering goal setting.'
    ],
    'email_text' => [
        'ERROR' => 'Error',
        'SUCCESS' => 'Success',
        'JOB_FOR' => 'Job For ',
        'PASSED' => 'Passed',
        'FAILED' => 'Failed',
        'TENANT' => 'tenant',
        'BACKGROUND_JOB_NAME' => 'Background Job Name',
        'BACKGROUND_JOB_STATUS' => 'Background Job Status',
        'COMPILE_SCSS_FILES' => 'Compile SCSS Files',
        'CREATE_FOLDER_ON_S3_BUCKET' => 'Create Folder On S3 Bucket',
        'TENANT_DEFAULT_LANGUAGE' => 'Tenant Default Language',
        'TENANT_MIGRATION' => 'Tenant Migration',
        'JOB_PASSED_SUCCESSFULLY' => 'Job passed successufully',
        'ALL_JOBS_EXECUTED_SUCCESSFULLY' => 'All background jobs executed successfully',
        'ON_BACKGROUND_JOBS' => 'On background jobs for',
        'MESSAGE' => 'Message',
        'BACKGROUND_JOB_EXCEPTION_MESSAGE' => 'Background job exception message'
    ]

];
