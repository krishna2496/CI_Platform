<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'email'                => 'The :attribute must be a valid email address.',
    'filled'               => 'The :attribute field is required.',
    'exists'               => 'The selected :attribute is invalid.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'url'                  => 'The :attribute format is invalid.',
    'present'              => 'The :attribute field is required',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'uuid'                 => 'Please use valid UUID string for :attribute',
    'ip_whitelist_pattern' => 'The :attribute field is in invalid format. Example: (216.109.112.0-135, 216.109.112.0/24, 216.109.*.*)',
    'max_item'             => 'The record count may not be greater than :max_item.',
    'within_range'         => 'The :attribute field must have a value between :minvalue and :maxvalue.',
    'prefix_with'        => 'The :attribute field must start with ":prefix_with".',
    'max_html_stripped'    => 'The :attribute may not be greater than :max characters.',
    'date_range'    => 'The :attribute data format is invalid. Example: YYYY-MM-DD:2020-12-20',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'media_images.*.media_path' => [
            'valid_media_path' => 'Please enter valid media image',
        ],
        'documents.*.document_path' => [
            'valid_document_path' => 'Please enter valid document file',
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => 'Please enter valid youtube url',
        ],
		'avatar' => [
            'valid_profile_image' => 'Invalid image file or image type is not allowed. Allowed types: png, jpeg, jpg',
        ],
		'parent_skill' => [
            'valid_parent_skill' => 'Invalid parent skill',
        ],
        'url' => [
            'valid_media_path' => 'Please enter valid image url',
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => 'Please enter valid linkedIn url',
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => 'Please select valid timesheet documents',
            'max' => 'Document file size must be ' .
            (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024) . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => 'You cannot add time entry for future dates',
        ],
        'news_image' => [
            'valid_media_path' => 'Please enter valid media image',
        ],
        'user_thumbnail' => [
            'valid_media_path' => 'Please enter valid media image',
        ],
        'story_images.*' => [
            'valid_story_image_type' => 'Please select valid image type',
            'max' => 'Image size must be ' .
            (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024) . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => 'Please enter valid video url',
            'max_video_url' => 'Maximum '.config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => 'Maximum '.config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
        ],
        'impact.*.icon_path' => [
            'valid_icon_path' => 'Please enter a valid icon image.',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'page_details.slug' => 'slug',
        'page_details.translations' => 'translations',
        'page_details.translations.*.lang' => 'language code',
        'page_details.translations.*.title' => 'title',
        'page_details.translations.*.sections' => 'sections',
        'translations.*.values' => 'values',
        'media_images.*.media_name' => 'media name',
        'media_images.*.media_type' => 'media type',
        'media_images.*.media_path' => 'media path',
        'media_videos.*.media_name' => 'media name',
        'media_videos.*.media_type' => 'media type',
        'media_videos.*.media_path' => 'media path',
        'documents.*.document_name' => 'document name',
        'documents.*.document_type' => 'document type',
        'documents.*.document_path' => 'document path',        
        'slider_detail.translations.*.lang' => 'language code',
        'skills.*.skill_id' => 'skill id',
        'location.city' => 'city',
        'location.state' => 'state',
        'location.country' => 'country',
        'password_confirmation' => 'confirm password',
        'translations.*.lang' => 'language code',
        'is_mandatory' => 'mandatory',
		'page_details.translations.*.sections.*.title' => 'title',
		'page_details.translations.*.sections.*.description' => 'description',
		'location.city_id' => 'city',
        'location.state_id' => 'state',
		'location.country_code' => 'country code',
		'organisation.organisation_id' => 'organisation id',
		'mission_detail.*.lang' => 'language code',
        'to_user_id' => 'user id',
        'custom_fields.*.field_id' => 'field id',
        'settings.*.tenant_setting_id' => 'tenant setting id',
        'settings.*.value' => 'value',
        'option_value.translations.*.lang' => 'language code',
        'option_value.translations.*.message' => 'option value',
        'option_value.position' => 'custom login text position',
        'timesheet_entries.*.timesheet_id' => 'timesheet id',
		'mission_detail.*.short_description' => 'short description',
        'news_content.translations' => 'translations',
        'news_content.translations.*.lang' => 'language code',
        'news_content.translations.*.title' => 'title',
        'news_content.translations.*.description' => 'description',
        'translations.*.title' => 'title',
        'settings.*.notification_type_id' => 'notification type id',
        'user_ids.*' => 'user id',
        'mission_detail.*.custom_information' => 'custom information',
        'mission_detail.*.custom_information.*.title' => 'title',
        'mission_detail.*.custom_information.*.description' => 'description',
        'mission_detail.*.title' => 'title',
        'organisation.organisation_name' => 'organisation name',
        'cities.*.translations.*.lang' => 'language code',
        'cities.*.translations.*.name' => 'name',
        'cities.*.translations' => 'translations',
        'media_images.*.sort_order' => 'sort order',
        'media_videos.*.sort_order' => 'sort order',
        'documents.*.sort_order' => 'sort order', 
        'countries.*.translations.*.lang' => 'language code',
        'countries.*.translations.*.name' => 'name',
        'countries.*.translations' => 'translations',   
        'countries.*.iso' => 'ISO',
        'translations.*.lang' => 'language code',
        'translations.*.name' => 'name',
        'translations' => 'translations',
        'mission_detail.*.section' => 'section',
        'mission_detail.*.section.*.title' => 'title',
        'mission_detail.*.section.*.description' => 'description',
        'states.*.translations.*.lang' => 'language code',
        'states.*.translations.*.name' => 'name',
        'states.*.translations' => 'translations',
        'mission_detail.*.label_goal_objective' => 'label goal objective',
        'mission_detail.*.label_goal_achieved' => 'label goal achieved',
        'impact_donation.*.amount' => 'impact_donation amount',
        'impact_donation.*.translations' => 'impact_donation translations',
        'impact_donation.*.translations.*.language_code' => 'impact_donation language_code',
        'impact_donation.*.translations.*.content' => 'impact_donation content',
        'impact_donation.*.impact_donation_id' => 'impact_donation_id',
        'impact.*.icon_path' => 'icon path',
        'impact.*.sort_key' => 'impact sort key',
        'impact.*.translations' => 'translations',
        'impact.*.translations.*.language_code' => 'language code',
        'impact.*.translations.*.content' => 'content',
        'impact.*.mission_impact_id' => 'mission impact id',
        'un_sdg.*' => 'UN SDG number',
        'organization.name' => 'organization name',
        'organization.organization_id' => 'organization id',
        'organization.legal_number' => 'organization legal number',
        'organization.phone_number' => 'organization phone number',
        'organization.address_line_1' => 'organization address line 1',
        'organization.address_line_2' => 'organization address line 2',
        'organization.city_id' => 'organization city id',
        'organization.state_id' => 'organization state id',
        'organization.country_id' => 'organization country id',
        'organization.postal_code' => 'organization postal code',
        'volunteering_attribute.availability_id' => 'availability id',
        'volunteering_attribute.is_virtual' => 'is virtual',
        'volunteering_attribute.total_seats' => 'total seats',
        'mission_tabs.*.translations' => 'mission tab translation',
        'mission_tabs.*.sort_key' => 'mission_tabs sort key',
        'mission_tabs.*.languages.*.language_id' => 'language id',
        'mission_tabs.*.languages.*.name' => 'name',
        'mission_tabs.*.translations.*.lang' => 'language code',
        'mission_tabs.*.translations.*.name' => 'name',
        'mission_tabs.*.translations.*.sections' => 'section details',
        'mission_tabs.*.translations.*.sections.*.title' => 'section title',
        'mission_tabs.*.translations.*.sections.*.content' => 'section content',
        'mission_tabs.*.mission_tab_id' => 'mission tab id',
        'donation_attribute' => 'donation_attribute',
        'donation_attribute.goal_amount_currency' => 'goal_amount_currency',
        'donation_attribute.goal_amount' => 'goal_amount',
        'donation_attribute.show_goal_amount' => 'show_goal_amount',
        'donation_attribute.show_donation_percentage' => 'show_donation_percentage',
        'donation_attribute.show_donation_meter' => 'show_donation_meter',
        'donation_attribute.show_donation_count' => 'show_donation_count',
        'donation_attribute.show_donors_count' => 'show_donors_count',
        'donation_attribute.disable_when_funded' => 'disable_when_funded',
        'donation_attribute.is_disabled' => 'is_disabled'
	]
];
