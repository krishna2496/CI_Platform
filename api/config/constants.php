<?php

return [
    /*
     * constants to use any where in system
     */
    'TENANT_OPTION_SLIDER' => 'slider',
    'TENANT_OPTION_CUSTOM_LOGIN_TEXT' => 'custom_login_text',
    'FORGOT_PASSWORD_EXPIRY_TIME' => '4',
    'SLIDER_LIMIT' => '4',
    'SLIDER_IMAGE_PATH' => 'images/',
    'ACTIVE' => 1,
    'DB_DATE_TIME_FORMAT' => 'Y-m-d H:i:s',
    'DB_DATE_FORMAT' => 'Y-m-d',
    'DB_TIME_FORMAT' => 'H:i:s',
    'PER_PAGE_LIMIT' => '9',
    'FRONT_DATE_FORMAT' => 'd/m/Y',
    'RELATED_MISSION_LIMIT' => '3',
    'MISSION_MEDIA_LIMIT' => '20',
    'SKILL_LIMIT' => '15',
    'TIMESHEET_DOCUMENT_SIZE_LIMIT' => '4096',
    'TIMESHEET_DATE_FORMAT' => 'Y-m-d',
    'TIMESHEET_DATE_TIME_FORMAT' => 'Y-m-d H:i:s',
    'NEWS_SHORT_DESCRIPTION_WORD_LIMIT' => 10,
    'STORY_IMAGE_SIZE_LIMIT' => '4096',
    'STORY_MAX_IMAGE_LIMIT' => 20,
    'STORY_MAX_VIDEO_LIMIT' => 20,

    'EMAIL_TEMPLATE_FOLDER' => 'emails',
    'EMAIL_TEMPLATE_USER_INVITE' => 'invite',
    'EMAIL_TEMPLATE_STORY_USER_INVITE' => 'invite-story',

    'AWS_S3_DOCUMENTS_FOLDER_NAME' => 'documents',
    'AWS_S3_SCSS_FOLDER_NAME' => 'scss',
    'AWS_S3_LOGO_IMAGE_NAME' => 'logo.png',
    'AWS_S3_CUSTOME_CSS_NAME' => 'style.css',
    'AWS_CUSTOM_STYLE_VARIABLE_FILE_NAME' => '_custom-variables.scss',
    'AWS_S3_CUSTOM_FAVICON_NAME' => 'favicon.ico',
    'TIMEZONE' => 'UTC',
    'MISSION_COMMENT_LIMIT' => 20,
    'AWS_S3_DEFAULT_PROFILE_IMAGE' => 'user.png',
    'FRONT_MISSION_DETAIL_URL' => '.anasource.com/team4/ciplatform/mission-detail/',
    'FRONT_HOME_URL' => '.anasource.com/team4/ciplatform/',
    'DEFAULT_FQDN_FOR_FRONT' => 'web8',
    'PER_PAGE_MAX' => '500',
    'MESSAGE_DATE_FORMAT' => 'Y-m-d',
    'DEFAULT_USER_HOURS_GOAL' => '500',
    'AWS_S3_LANGUAGES_FOLDER_NAME' => 'languages',
    'AWS_S3_LANGUAGE_FILE_EXTENSION' => '.json',
    'AWS_S3_DEFAULT_LANGUAGE_FOLDER_NAME' => 'default_language',
    'PER_PAGE_ALL' => '100000',
    'SUPPORT_EMAIL' => 'support@optimy.com',

    /*
     * User custom field types
     */
     'custom_field_types' => [
        'TEXT' => 'text',
        'EMAIL' => 'email',
        'DROP-DOWN' => 'drop-down',
        'RADIO' => 'radio',
        'CHECKBOX' => 'checkbox',
        'MULTISELECT' => 'multiselect',
        'TEXTAREA' => 'textarea',
     ],

     /*
      * Language constants
      */

    'DEFAULT_LANGUAGE' => 'EN',
    'FRONTEND_LANGUAGE_FOLDER' => 'front_end',

    /*
     * Comments approval status
     */
    'comment_approval_status' => [
        'PENDING' => 'PENDING',
        'PUBLISHED' => 'PUBLISHED',
        'DECLINED' => 'DECLINED'
    ],

    /*
     * Mission types
     */
    'mission_type' => [
        'TIME' => 'TIME',
        'GOAL' => 'GOAL',
        'DONATION' => 'DONATION',
        'EAF' => 'EAF',
        'DISASTER_RELIEF' => 'DISASTER_RELIEF'
    ],

    'volunteering_mission_types' => [
        'TIME',
        'GOAL'
    ],

    'donation_mission_types' => [
        'DONATION',
        'EAF',
        'DISASTER_RELIEF'
    ],

    /*
     * Publication status
     */
    'publication_status' => [
        'DRAFT' => 'DRAFT',
        'PENDING_APPROVAL' => 'PENDING_APPROVAL',
        'REFUSED' => 'REFUSED',
        'APPROVED' => 'APPROVED',
        'PUBLISHED_FOR_VOTING' => 'PUBLISHED_FOR_VOTING',
        'PUBLISHED_FOR_APPLYING' => 'PUBLISHED_FOR_APPLYING',
        'UNPUBLISHED' => 'UNPUBLISHED'
    ],

    /*
     * Day volunteered types
     */
    'day_volunteered' => [
        'WORKDAY' => 'WORKDAY',
        'HOLIDAY' => 'HOLIDAY',
        'WEEKEND' => 'WEEKEND'
    ],

    /*
     * Image types
     */
    'image_types' => [
        'PNG' => 'png',
        'JPG' => 'jpg',
        'JPEG' => 'jpeg'
    ],

    /*
     * Story image types
     */
    'story_image_types' => [
        'PNG' => 'png',
        'JPG' => 'jpg',
        'JPEG' => 'jpeg'
    ],

    /*
     * Slider image types
     */
    'slider_image_types' => [
        'PNG' => 'png',
        'JPG' => 'jpg',
        'JPEG' => 'jpeg'
    ],

    /*
     * Slider image types
     */
    'slider_image_mime_types' => [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/svg+xml'
    ],

    /*
     * Custom login text positions
     */
    'custom_login_text_positions' => [
        'after_login_form',
        'before_logo',
        'after_logo'
    ],

    /*
     * User profile image allowed MIME types
     */
    'profile_image_types' => [
        'image/png',
        'image/jpeg',
        'image/jpg'
    ],

    /*
     * Document types
     */
    'document_types' => [
        'DOC' => 'doc',
        'DOCX' => 'docx',
        'XLS' => 'xls',
        'XLSX' => 'xlsx',
        'PDF' => 'pdf',
        'TXT' => 'txt'
    ],

    /*
     * Document types
     */
    'document_mime_types' => [
        'application/vnd.ms-word.document.macroenabled.12',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel.sheet.binary.macroenabled.12',
        'application/vnd.ms-excel.sheet.macroenabled.12',
        'application/pdf',
        'text/plain'
    ],

    /*
     * Timesheet document types
     */
    'timesheet_document_types' => [
        'DOC' => 'doc',
        'DOCX' => 'docx',
        'XLS' => 'xls',
        'XLSX' => 'xlsx',
        'CSV' => 'csv',
        'PNG' => 'png',
        'PDF' => 'pdf',
        'JPG' => 'jpg',
        'JPEG' => 'jpeg'
    ],

    /*
     * Application status
     */
    'application_status' => [
        'AUTOMATICALLY_APPROVED' => 'AUTOMATICALLY_APPROVED',
        'PENDING' => 'PENDING',
        'REFUSED' => 'REFUSED'
    ],

    /*
     * Timesheet status
     */
    'timesheet_status' => [
        'AUTOMATICALLY_APPROVED' => 'AUTOMATICALLY_APPROVED',
        'PENDING' => 'PENDING',
        'DECLINED' => 'DECLINED',
        'APPROVED' => 'APPROVED',
        'SUBMIT_FOR_APPROVAL' => 'SUBMIT_FOR_APPROVAL'
    ],

    'ALLOW_TIMESHEET_ENTRY' => 2,

    /*
     * Export timesheet file names
     */
    'export_timesheet_file_names' => [
        'PENDING_TIME_MISSION_ENTRIES_XLSX' => 'Pending_Time_Mission_Entries.xlsx',
        'PENTIND_GOAL_MISSION_ENTRIES_XLSX' => 'Pending_Goal_Mission_Entries.xlsx',
        'TIME_MISSION_HISTORY_XLSX' => 'Time_Mission_History.xlsx',
        'GOAL_MISSION_HISTORY_XLSX' => 'Goal_Mission_History.xlsx'
    ],

    /*
     * News status
     */
    'news_status' => [
        'PUBLISHED' => 'PUBLISHED',
        'UNPUBLISHED' => 'UNPUBLISHED'
    ],

    /*
     * Story status
     */
    'story_status' => [
        'DRAFT' => 'DRAFT',
        'PENDING' => 'PENDING',
        'PUBLISHED' => 'PUBLISHED',
        'DECLINED' => 'DECLINED'
    ],

    /*
     * Export story file names
     */
    'export_story_file_names' => [
        'STORY_XLSX' => 'Stories.xlsx'
    ],

    /*
     * Export mission comments file names
     */
    'export_mission_comment_file_names' => [
        'MISSION_COMMENT_XLSX' => 'MissionComments.xlsx'
    ],

    /*
     * Folder name s3
     */
    'folder_name' => [
        'timesheet' => 'timesheet',
        'story' => 'story'
    ],

    /*
     * Story status
     */
    'story_status' => [
        'DRAFT' => 'DRAFT',
        'PUBLISHED' => 'PUBLISHED',
        'PENDING' => 'PENDING',
        'DECLINED' => 'DECLINED'
    ],

    /*
     * send message froms
     */
    'message' => [
        'read' => '1',
        'unread' => '0',
        'anonymous' => '1',
        'not_anonymous' => '0',
        'send_message_from' => [
            'all' => 0,
            'user' => 1,
            'admin' => 2
        ],
    ],

    /*
     * User notification types
     */
    'notification_types' => [
        'RECOMMENDED_MISSIONS' => 'Recommended missions',
        'VOLUNTEERING_HOURS' => 'Volunteering hours',
        'VOLUNTEERING_GOALS' => 'Volunteering goals',
        'MY-COMMENTS' => 'My comments',
        'MY-STORIES' => 'My stories',
        'NEW_STORIES_HOURS' => 'New stories hours',
        'NEW_MISSIONS' => 'New missions',
        'NEW_MESSAGES' => 'New messages',
        'RECOMMENDED_STORY' => 'Recommended story',
        'MISSION_APPLICATION' => 'Mission Application',
        'NEW_NEWS' => 'New News'
    ],

    /*
     * notification status
     */
    'notification' => [
        'read' => '1',
        'unread' => '0'
    ],

    /*
     * Tenant settings
     */
    'tenant_settings' => [
        'DONATION' => 'donation',
        'EMAIL_NOTIFICATION_INVITE_COLLEAGUE' => 'email_notification_invite_colleague',
        'INVITE_COLLEAGUE' => 'invite_colleague',
        'MESSAGE_ENABLED' => 'message_enabled',
        'MISSION_COMMENT_AUTO_APPROVED' => 'mission_comment_auto_approved',
        'MISSION_COMMENTS' => 'mission_comments',
        'MISSION_IMPACT' => 'mission_impact',
        'MISSION_RATING_VOLUNTEER' => 'mission_rating_volunteer',
        'NEWS_ENABLED' => 'news_enabled',
        'STATE_ENABLED' => 'state_selection',
        'STORIES_ENABLED' => 'stories_enabled',
        'VOLUNTEERING_GOAL_MISSION' => 'volunteering_goal_mission',
        'VOLUNTEERING_TIME_MISSION' => 'volunteering_time_mission',
        'VOLUNTEERING' => 'volunteering',
        'DONATION_MISSION' => 'donation',
        'VOLUNTEERING_MISSION' => 'volunteering',
        'EAF' => 'eaf',
        'DISASTER_RELIEF' => 'disaster_relief',
        'IMPACT_DONATION' => 'impact_donation'
    ],

    'TOP_THEME' => "top_themes",
    'TOP_COUNTRY' => "top_countries",
    'TOP_ORGANISATION' => "top_organization",
    'MOST_RANKED' => "most-ranked-missions",
    'TOP_FAVOURITE' => "favourite-missions",
    'TOP_RECOMMENDED' => "recommended-missions",
    'THEME' => "themes",
    'COUNTRY' => "country",
    'CITY' => "city",
    'SKILL' => "skill",
    'RANDOM' => 'random-missions',
    'STATE' => 'state',
    'VIRTUAL' => 'virtual-missions',
    /* sort by */
    'NEWEST' => 'newest',
    'OLDEST' => 'oldest',
    'LOWEST_AVAILABLE_SEATS' => 'lowest_available_seats',
    'HIGHEST_AVAILABLE_SEATS' => 'highest_available_seats',
    'MY_FAVOURITE' => 'my_favourite',
    'DEADLINE' => 'deadline',

    'ORGANIZATION' => 'organization',
    'EXPLORE_MISSION_LIMIT' => '5',
    'IMAGE' => 'image',

    'error_codes' => [
        'ERROR_FOOTER_PAGE_REQUIRED_FIELDS_EMPTY' => '300000',
        'ERROR_INVALID_ARGUMENT' => '300002',
        'ERROR_FOOTER_PAGE_NOT_FOUND' => '300003',
        'ERROR_DATABASE_OPERATIONAL' => '300004',
        'ERROR_NO_DATA_FOUND' => '300005',
        'ERROR_NO_DATA_FOUND_FOR_SLUG' => '300006',
        'ERROR_USER_NOT_FOUND' => '100000',
        'ERROR_SKILL_INVALID_DATA' => '100002',
        'ERROR_USER_CUSTOM_FIELD_INVALID_DATA' => '100003',
        'ERROR_USER_CUSTOM_FIELD_NOT_FOUND' => '100004',
        'ERROR_USER_INVALID_DATA' => '100010',
        'ERROR_USER_SKILL_NOT_FOUND' => '100011',
        'ERROR_SLIDER_IMAGE_UPLOAD' => '100012',
        'ERROR_SLIDER_INVALID_DATA' => '100013',
        'ERROR_SLIDER_LIMIT' => '100014',
        'ERROR_NOT_VALID_EXTENSION' => '100015',
        'ERROR_FILE_NAME_NOT_MATCHED_WITH_STRUCTURE' => '100016',
        'ERROR_INVALID_IMAGE_URL' => '100017',
        'ERROR_SLIDER_NOT_FOUND' => '100018',
        'ERROR_INVALID_EXTENSION_OF_FILE' => '100020',
        'ERROR_INVALID_API_AND_SECRET_KEY' => '210000',
        'ERROR_API_AND_SECRET_KEY_REQUIRED' => '210001',
        'ERROR_EMAIL_NOT_EXIST' => '210002',
        'ERROR_INVALID_RESET_PASSWORD_LINK' => '210003',
        'ERROR_RESET_PASSWORD_INVALID_DATA' => '210004',
        'ERROR_SEND_RESET_PASSWORD_LINK' => '210005',
        'ERROR_INVALID_DETAIL' => '210006',
        'ERROR_TENANT_DOMAIN_NOT_FOUND' => '210008',
        'ERROR_TOKEN_EXPIRED' => '210009',
        'ERROR_IN_TOKEN_DECODE' => '210010',
        'ERROR_TOKEN_NOT_PROVIDED' => '210012',
        'ERROR_INVALID_EMAIL_OR_PASSWORD' => '210013',
        'ERROR_USER_EXPIRED' => '210014',
        'ERROR_USER_BLOCKED' => '210015',
        'ERROR_USER_ACTIVE' => '210016',
        'ERROR_USER_INVITE_INVALID_DATA' => '210017',
        'ERROR_ACCOUNT_EXPIRED' => '210018',
        'ERROR_MAXIMUM_USERS_REACHED' =>  '210019',
        'ERROR_MAX_ATTEMPTS_REACHED' =>  '210019',

        'ERROR_INVALID_MISSION_APPLICATION_DATA' => '400000',
        'ERROR_INVALID_MISSION_DATA' => '400001',
        'ERROR_MISSION_NOT_FOUND' => '400003',
        'ERROR_MISSION_REQUIRED_FIELDS_EMPTY' => '400006',
        'ERROR_NO_MISSION_FOUND' => '400007',
        'ERROR_THEME_INVALID_DATA' => '400008',
        'ERROR_THEME_NOT_FOUND' => '400009',
        'ERROR_NO_SKILL_FOUND' => '400010',
        'ERROR_SKILL_DELETION' => '400011',
        'ERROR_SKILL_REQUIRED_FIELDS_EMPTY' => '400012',
        'ERROR_SKILL_NOT_FOUND' => '400014',
        'ERROR_INVALID_MISSION_ID' => '400018',
        'ERROR_MISSION_APPLICATION_SEATS_NOT_AVAILABLE' => '400021',
        'ERROR_INVALID_INVITE_MISSION_DATA' => '400019',
        'ERROR_INVITE_MISSION_ALREADY_EXIST' => '400020',
        'ERROR_MISSION_APPLICATION_DEADLINE_PASSED' => '400022',
        'ERROR_MISSION_APPLICATION_ALREADY_ADDED' => '400023',
        'ERROR_MISSION_APPLICATION_NOT_FOUND' => '400024',
        'ERROR_MISSION_RATING_INVALID_DATA' => '400025',
        'ERROR_MISSION_COMMENT_INVALID_DATA' => '400026',
        'ERROR_COMMENT_NOT_FOUND' => '400029',
        'ERROR_SKILL_LIMIT' => '400030',
        'ERROR_TIMESHEET_REQUIRED_FIELDS_EMPTY' => '400031',
        'ERROR_INVALID_ACTION' => '400032',
        'TIMESHEET_NOT_FOUND' => '400033',
        'ERROR_TIMESHEET_ALREADY_APPROVED' => '400034',
        'TIMESHEET_DOCUMENT_NOT_FOUND' => '400035',
        'ERROR_TIMESHEET_ENTRY_NOT_FOUND' => '400036',
        'ERROR_MISSION_STARTDATE' => '400037',
        'ERROR_MISSION_ENDDATE' => '400038',
        'MISSION_APPLICATION_NOT_APPROVED' => '400039',
        'ERROR_TIMESHEET_ALREADY_DONE_FOR_DATE' => '400040',
        'ERROR_INVALID_DATA_FOR_TIMESHEET_ENTRY' => '400041',
        'ERROR_SAME_DATE_TIME_ENTRY' => '400042',
        'ERROR_UNAUTHORIZED_USER' => '400043',
        'ERROR_APPROVED_TIMESHEET_DOCUMENTS' => '400044',
        'ERROR_MISSION_MEDIA_NOT_FOUND' => '400045',
        'ERROR_MISSION_DOCUMENT_NOT_FOUND' => '400046',
        'ERROR_MEDIA_DEFAULT_IMAGE_CANNOT_DELETED' => '400047',
        'ERROR_MEDIA_ID_DOSENT_EXIST' => '400048',
        'ERROR_DOCUMENT_ID_DOSENT_EXIST' => '400049',
        'ERROR_MISSION_DEFAULT_LANGUAGE_CANNOT_DELETED' => '400050',
        'ERROR_SEND_USER_INVITE_LINK' => '400051',
        'ERROR_DONATION_STATISTICS_PARAMS_DATA' => '400052',
        'ERROR_FAILED_RETRIEVING_STATISTICS' => '400053',

        'ERROR_NEWS_CATEGORY_NOT_FOUND' => '500001',
        'ERROR_NEWS_CATEGORY_INVALID_DATA' => '500002',
        'ERROR_NEWS_REQUIRED_FIELDS_EMPTY' => '500003',
        'ERROR_NEWS_NOT_FOUND' => '500004',

        'ERROR_STORY_REQUIRED_FIELDS_EMPTY' => '700001',
        'ERROR_STORY_NOT_FOUND' => '700002',
        'ERROR_COPY_DECLINED_STORY' => '700004',
        'ERROR_STORY_PUBLISHED_OR_DECLINED' => '700005',
        'ERROR_STORY_IMAGE_NOT_FOUND' => '700006',
        'ERROR_STORY_IMAGE_DELETE' => '700007',
        'ERROR_SUBMIT_STORY_PUBLISHED_OR_DECLINED' => '700008',
        'ERROR_INVALID_INVITE_STORY_DATA' => '700009',
        'ERROR_INVITE_STORY_ALREADY_EXIST' => '700010',
        'ERROR_SUBMIT_STORY_INVALID' => '700011',

        'ERROR_CONTACT_FORM_REQUIRED_FIELDS_EMPTY' => '1000001',

        'ERROR_USER_NOTIFICATION_REQUIRED_FIELDS_EMPTY' => '600001',
        'ERROR_USER_NOTIFICATION_NOT_FOUND' => '600002',

        'ERROR_INVALID_JSON' => '900000',
        'ERROR_TENANT_SETTING_DISABLED' => '900001',

        'ERROR_TENANT_ASSET_FOLDER_NOT_FOUND_ON_S3' => '800009',
        'ERROR_NO_FILES_FOUND_IN_ASSETS_FOLDER' => '800010',
        'ERROR_TENANT_SETTING_REQUIRED_FIELDS_EMPTY' => '800012',
        'ERROR_SETTING_FOUND' => '800013',
        'ERROR_IMAGE_FILE_NOT_FOUND_ON_S3' => '800014',
        'ERROR_IMAGE_UPLOAD_INVALID_DATA' => '800017',
        'ERROR_TENANT_OPTION_REQUIRED_FIELDS_EMPTY' => '800018',
        'ERROR_TENANT_OPTION_NOT_FOUND' => '800019',
        'ERROR_COUNTRY_NOT_FOUND' => '800021',
        'ERROR_REQUIRED_FIELDS_FOR_UPDATE_STYLING' => '800023',
        'ERROR_POLICY_PAGE_NOT_FOUND' => '300010',
        'ERROR_POLICY_PAGE_REQUIRED_FIELDS_EMPTY' => '300011',
        'ERROR_MESSAGE_REQUIRED_FIELDS_EMPTY' => '1100001',
        'ERROR_MESSAGE_USER_MESSAGE_NOT_FOUND' => '1100002',
        'ERROR_ACTIVITY_LOG_REQUIRED_FIELDS_EMPTY' => '1200001',
        'ERROR_AVAILABILITY_INVALID_DATA' => '410001',
        'ERROR_AVAILABILITY_NOT_FOUND' => '410002',
        'ERROR_CITY_INVALID_DATA' => '800024',
        'ERROR_COUNTRY_INVALID_DATA' => '800025',
        'ERROR_CITY_NOT_FOUND' => '800026',
        'ERROR_CITY_ENABLE_TO_DELETE' => '800027',
        'ERROR_COUNTRY_ENABLE_TO_DELETE' => '800028',
        'ERROR_TENANT_LANGUAGE_FOLDER_NOT_FOUND_ON_S3' => '800029',
        'ERROR_TENANT_LANGUAGE_FILE_NOT_FOUND_ON_S3' => '800030',
        'ERROR_TENANT_LANGUAGE_FILE_UPLOAD_INVALID_DATA' => '800031',
        'ERROR_NOT_VALID_TENANT_LANGUAGE_FILE_EXTENSION' => '800032',
        'ERROR_TENANT_LANGUAGE_INVALID_JSON_FORMAT' => '800033',
        'ERROR_TENANT_LANGUAGE_INVALID' => '800034',
        'ERROR_TENANT_LANGUAGE_INVALID_CODE' => '800035',
        'ERROR_INCOMPLETE_LANGUAGE_FILE' => '800036',
        'ERROR_STATE_INVALID_DATA' => '800037',
        'ERROR_STATE_NOT_FOUND' => '800038',
        'ERROR_STATE_ENABLE_TO_DELETE' => '800039',
        'ERROR_INVALID_SAML_IDENTITY_PROVIDER' => '800100',
        'ERROR_INVALID_SAML_ARGS_LANGUAGE' => '800101',
        'ERROR_INVALID_SAML_ARGS_TIMEZONE' => '800102',
        'ERROR_INVALID_SAML_ARGS_COUNTRY' => '800103',
        'ERROR_INVALID_SAML_ACCESS' => '800104',
        'ERROR_UNAUTHORIZED_LOGIN_METHOD' => '800105',
        'ERROR_SAML_ACCESS_ONLY_ACTIVE' => '800106',
        'ERROR_STATE_UNABLE_TO_DELETE' => '800039',
        'ERROR_THEME_UNABLE_TO_DELETE' => '800040',
        'ERROR_SKILL_UNABLE_TO_DELETE' => '800041',
        'ERROR_AVAILABILITY_UNABLE_TO_DELETE' => '800042',

        'IMPACT_DONATION_MISSION_NOT_FOUND' => '400051',
        'IMPACT_MISSION_NOT_FOUND' => '400060',
        'ERROR_ORGANIZATION_REQUIRED_FIELDS_EMPTY' => '800043',
        'ERROR_ORGANIZATION_NOT_FOUND' => '800044',
        'ERROR_ORGANIZATION_LINKED_TO_MISSION' => '80045',
        'MISSION_TAB_NOT_FOUND' => '80046',
        'ERROR_IMPACT_SORT_KEY_ALREADY_EXIST' => '80047',
        'ERROR_RETRIEVING_TENANT_ACTIVATED_CURRENCIES' => '80048',
        'ERROR_INVALID_CURRENCY' => '80049',
        'ERROR_SORT_KEY_ALREADY_EXIST' => '80050',
        'ERROR_VOLUNTEERING_TIME_OR_GOAL_SHOULD_BE_ACTIVE' => '80051',
        'ERROR_VOLUNTEERING_SHOULD_BE_ENABLED' => '80052',
        'ERROR_ORGANIZATION_UPDATE_WITHOUT_ACCOUNT' => '80053',

        // Donation / Payment Gateway error codes: 900100 - 900199
        'ERROR_DONATION_IP_WHITELIST_INVALID_DATA' => '900100',
        'ERROR_DONATION_IP_WHITELIST_NOT_FOUND' => '900101',
        'ERROR_IP_ADDRESS_NOT_ALLOWED' => '900102',
        'ERROR_PAYMENT_GATEWAY_ACCOUNT_INVALID' => '900103',

        'ERROR_PAYMENT_GATEWAY_CARD_DECLINED' => '900104',
        'ERROR_PAYMENT_GATEWAY_CONNECTION_FAILED' => '900105',
        'ERROR_PAYMENT_GATEWAY_INTERNAL_FAILURE' => '900106',
        'ERROR_PAYMENT_GATEWAY_INVALID_REQUEST' => '900107',
        'ERROR_PAYMENT_GATEWAY_RATE_LIMITED' => '900108',
        'ERROR_PAYMENT_GATEWAY_UNAUTHORIZED' => '900109',
        'ERROR_PAYMENT_GATEWAY_UNKNOWN_FAILURE' => '900110',

        'ERROR_PAYMENT_METHOD_NOT_FOUND' => '900111',
        'ERROR_PAYMENT_METHOD_INVALID_DATA' => '900112',
        'ERROR_PAYMENT_METHOD_UNKNOWN_ERROR' => '900113',

        'ERROR_INVALID_PAYMENT_DATA' => '900114',
        'ERROR_FAILED_CREATING_PAYMENT_OBJECT' =>  '900115',
        'ERROR_FAILED_SAVING_PAYMENT_RECORD' => '900116',
        'ERROR_PAYMENT_NOT_FOUND' => '900117',
        'ERROR_PAYMENT_METHOD_NOT_FOUND' => '900118',
        'ERROR_PAYMENT_ORGANIZATION_DOES_NOT_SUPPORT_DONATION' => '900119',
        'ERROR_ORGANIZATION_PAYMENT_GATEWAY_ACCOUNT' => '900120',
        'ERROR_PAYMENT_MISSION_NOT_ELIGIBLE_FOR_DONATION' => '900120',
    ],

    /**
     * Notification types
     */
    'notification_type_keys' => [
        'RECOMMENDED_MISSIONS' => 'recommended_missions',
        'VOLUNTEERING_HOURS' => 'volunteering_hours',
        'VOLUNTEERING_GOALS' => 'volunteering_goals',
        'MY_COMMENTS' => 'my_comments',
        'MY_STORIES' => 'my_stories',
        'NEW_MISSIONS' => 'new_missions',
        'NEW_MESSAGES' => 'new_messages',
        'RECOMMENDED_STORY' => 'recommended_story',
        'MISSION_APPLICATION' => 'mission_application',
        'NEW_NEWS' => 'new_news'
    ],

    /*
     * Notification actions
     */
    'notification_actions' => [
        'CREATED' => 'CREATED',
        'APPROVED' => 'APPROVED',
        'REJECTED' => 'REJECTED',
        'PUBLISHED' => 'PUBLISHED',
        'PENDING' => 'PENDING',
        'DECLINED' => 'DECLINED',
        'INVITE' => 'INVITE',
        'AUTOMATICALLY_APPROVED' => 'AUTOMATICALLY_APPROVED',
        'SUBMIT_FOR_APPROVAL' => 'SUBMIT_FOR_APPROVAL',
        'DELETED' => 'DELETED',
        'UPDATED' => 'UPDATED',
        'REFUSED' => 'REFUSED',
        'PUBLISHED_FOR_APPLYING' => 'PUBLISHED_FOR_APPLYING'
    ],

    /**
     * Notification type icons
     */
    'notification_icons' => [
        'APPROVED' => 'approve-ic.png',
        'DECLINED' => 'warning.png',
        'NEW' => 'circle-plus.png'
    ],

    'notification_status' => [
        'AUTOMATICALLY_APPROVED' => 'AUTOMATICALLY_APPROVED',
        'PENDING' => 'PENDING',
        'DECLINED' => 'DECLINED',
        'APPROVED' => 'APPROVED',
        'REFUSED' => 'REFUSED',
        'PUBLISHED' => 'PUBLISHED',
        'SUBMIT_FOR_APPROVAL' => 'SUBMIT_FOR_APPROVAL'
    ],

    'activity_log_types' => [
        'AUTH' => 'AUTH',
        'USERS' => 'USERS',
        'MISSION' => 'MISSION',
        'COMMENT' => 'COMMENT',
        'MESSAGE' => 'MESSAGE',
        'USERS_CUSTOM_FIELD' => 'USERS_CUSTOM_FIELD',
        'USER_PROFILE' => 'USER_PROFILE',
        'USER_PROFILE_IMAGE' => 'USER_PROFILE_IMAGE',
        'NEWS_CATEGORY' => 'NEWS_CATEGORY',
        'NEWS' => 'NEWS',
        'VOLUNTEERING_TIMESHEET' => 'VOLUNTEERING_TIMESHEET',
        'VOLUNTEERING_TIMESHEET_DOCUMENT' => 'VOLUNTEERING_TIMESHEET_DOCUMENT',
        'SLIDER' => 'SLIDER',
        'STYLE_IMAGE' => 'STYLE_IMAGE',
        'STYLE' => 'STYLE',
        'TENANT_OPTION' => 'TENANT_OPTION',
        'TENANT_SETTINGS' => 'TENANT_SETTINGS',
        'FOOTER_PAGE' => 'FOOTER_PAGE',
        'POLICY_PAGE' => 'POLICY_PAGE',
        'MISSION_THEME' => 'MISSION_THEME',
        'SKILL' => 'SKILL',
        'USER_SKILL' => 'USER_SKILL',
        'USER_COOKIE_AGREEMENT' => 'USER_COOKIE_AGREEMENT',
        'GOAL_TIMESHEET' => 'GOAL_TIMESHEET',
        'TIME_TIMESHEET' => 'TIME_TIMESHEET',
        'TIME_MISSION_TIMESHEET' => 'TIME_MISSION_TIMESHEET',
        'GOAL_MISSION_TIMESHEET' => 'GOAL_MISSION_TIMESHEET',
        'STORY' => 'STORY',
        'MISSION_COMMENTS' => 'MISSION_COMMENTS',
        'STORY_IMAGE' => 'STORY_IMAGE',
        'STORY_VISITOR' => 'STORY_VISITOR',
        'NOTIFICATION_SETTING' => 'NOTIFICATION_SETTING',
        'NOTIFICATION' => 'NOTIFICATION',
        'AVAILABILITY' => 'AVAILABILITY',
        'COUNTRY' => 'COUNTRY',
        'CITY' => 'CITY',
        'MISSION_MEDIA' => 'MISSION_MEDIA',
        'MISSION_DOCUMENT' => 'MISSION_DOCUMENT',
        'TENANT_LANGUAGE' => 'TENANT_LANGUAGE',
        'STATE' => 'STATE',
        'ORGANIZATION' => 'ORGANIZATION',
        'MISSION_TAB' => 'MISSION_TAB',
        'DONATION_IP_WHITELIST' => 'DONATION_IP_WHITELIST',
        'MISSION_IMPACT' => 'MISSION_IMPACT',
        'MISSION_IMPACT_DONATION' => 'MISSION_IMPACT_DONATION',
        'PAYMENT_GATEWAY' => 'PAYMENT_GATEWAY',
        'PAYMENT_METHOD' => 'PAYMENT_METHOD'
    ],

    'activity_log_actions' => [
        'CREATED' => 'CREATED',
        'UPDATED' => 'UPDATED',
        'DELETED' => 'DELETED',
        'INVITED' => 'INVITED',
        'SUBMIT_FOR_APPROVAL' => 'SUBMIT_FOR_APPROVAL',
        'APPROVED' => 'APPROVED',
        'DECLINED' => 'DECLINED',
        'LOGIN' => 'LOGIN',
        'ADD_TO_FAVOURITE' => 'ADD_TO_FAVOURITE',
        'REMOVE_FROM_FAVOURITE' => 'REMOVE_FROM_FAVOURITE',
        'RATED' => 'RATED',
        'COMMENT_ADDED' => 'COMMENT_ADDED',
        'COMMENT_UPDATED' => 'COMMENT_UPDATED',
        'COMMENT_DELETED' => 'COMMENT_DELETED',
        'MISSION_APPLICATION_CREATED' => 'MISSION_APPLICATION_CREATED',
        'MISSION_APPLICATION_STATUS_CHANGED' => 'MISSION_APPLICATION_STATUS_CHANGED',
        'PASSWORD_RESET_REQUEST' => 'PASSWORD_RESET_REQUEST',
        'PASSWORD_CHANGED' => 'PASSWORD_CHANGED',
        'PASSWORD_RESET' => 'PASSWORD_RESET',
        'LINKED' => 'LINKED',
        'UNLINKED' => 'UNLINKED',
        'ACCEPTED' => 'ACCEPTED',
        'EXPORT' => 'EXPORT',
        'COPIED' => 'COPIED',
        'COUNTED' => 'COUNTED',
        'READ' => 'READ',
        'ACTIVATED' => 'ACTIVATED',
        'DEACTIVATED' => 'DEACTIVATED',
        'CLEAR_ALL' => 'CLEAR_ALL',
        'PASSWORD_UPDATED' => 'PASSWORD_UPDATED',
        'ATTACHED' => 'ATTACHED',
        'DETACHED' => 'DETACHED',
        'RETRIEVED' => 'RETRIEVED',
    ],

    'activity_log_user_types' => [
        'API' => 'API',
        'REGULAR' => 'REGULAR'
    ],

    'profile_required_fields' => [
        'first_name',
        'last_name',
        'email',
        'country_id',
        'timezone_id',
        'language_id'
    ],

    'user_statuses' => [
        'ACTIVE' => '1',
        'INACTIVE' => '0'
    ],

    /*
     * Icon image types
     */
    'icon_image_mime_types' => [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/svg+xml'
    ],

    'payment_gateway_types' => [
        'STRIPE' => 1
    ],

    'payment_statuses' => [
        'PENDING' => 0,
        'SUCCESS' => 1,
        'FAILED' => 2,
        'CANCELED' => 3
    ],

    'payment_method_types' => [
        'CARD' => 1
    ]
];
