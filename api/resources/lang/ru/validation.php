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

    'accepted'             => "Необходимо принять :attribute",
    'active_url'           => ":attribute – недействительный URL.",
    'after'                => ":attribute должно быть датой после :date.",
    'alpha'                => ":attribute может содержать только буквы.",
    'alpha_dash'           => ":attribute может содержать только буквы, цифры и дефисы.",
    'alpha_num'            => ":attribute может содержать только буквы и цифры.",
    'array'                => ":attribute должен быть массивом.",
    'before'               => ":attribute должно быть датой до :date.",
    'between'              => [
        'numeric' => ":attribute должен быть между :min и :max.",
        'file'    => ":attribute должен быть между :min и :max килобайт.",
        'string'  => ":attribute должен быть между :min и :max знаков.",
        'array'   => ":attribute должен быть между :min и :max пунктов.",
    ],
    'boolean'              => "Поле :attribute должно быть верно или неверно.",
    'confirmed'            => "Подтверждение :attribute не соответствует.",
    'date'                 => ":attribute – недействительная дата.",
    'date_format'          => ":attribute не соответствует формату :format.",
    'different'            => ":attribute и :other должны различаться.",
    'digits'               => ":attribute должен быть :digits цифрами.",
    'digits_between'       => ":attribute должен быть между :min и :max цифр.",
    'email'                => ":attribute должен быть действительным e-mail.",
    'filled'               => "Требуется поле :attribute.",
    'exists'               => "Выбранный :attribute недействителен.",
    'image'                => ":attribute должен быть изображением.",
    'in'                   => "Выбранный :attribute недействителен.",
    'integer'              => ":attribute должен быть целым числом.",
    'ip'                   => ":attribute должен быть действительным IP.",
    'max'                  => [
        'numeric' => ":attribute не может быть больше :max.",
        'file'    => ":attribute не может быть больше :max килобайт.",
        'string'  => ":attribute не может быть больше :max знаков.",
        'array'   => ":attribute не может быть больше :max пунктов.",
    ],
    'mimes'                => ":attribute должен быть файлом типа: :values.",
    'mimetypes'            => ":attribute должен быть файлом типа: :values.",
    'min'                  => [
        'numeric' => ":attribute должен быть не меньше :min.",
        'file'    => ":attribute должен быть не меньше :min килобайт.",
        'string'  => ":attribute должен быть не меньше :min знаков.",
        'array'   => ":attribute должен быть не меньше :min пунктов.",
    ],
    'not_in'               => "Выбранный :attribute недействителен.",
    'numeric'              => ":attribute должен быть числом.",
    'regex'                => "Формат :attribute недействителен.",
    'required'             => "Требуется поле :attribute.",
    'required_if'          => "Требуется поле :attribute, когда :other :value.",
    'required_with'        => "Требуется поле :attribute, когда имеется :values.",
    'required_with_all'    => "Требуется поле :attribute, когда имеется :values.",
    'required_without'     => "Требуется поле :attribute, когда не имеется :values.",
    'required_without_all' => "Требуется поле :attribute, когда не имеется ничего из :values.",
    'same'                 => ":attribute и :other должны совпадать.",
    'size'                 => [
        'numeric' => ":attribute должен быть :size.",
        'file'    => ":attribute должен быть :size килобайт.",
        'string'  => ":attribute должен быть :size знаков.",
        'array'   => ":attribute должен содержать :size пунктов.",
    ],
    'timezone'             => ":attribute должен быть действительной зоной.",
    'unique'               => ":attribute уже был взят.",
    'url'                  => "Формат :attribute недействителен.",
    'present'              => "Требуется поле :attribute",
	'distinct'             => "Поле :attribute имеет повторяющееся значение.",

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
            'valid_media_path' => "Введите действительную медиа-картинку",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Введите действительный файл документа",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Введите ссылку на действительный профиль youtube",
        ],
		'avatar' => [
            'valid_profile_image' => "Недействительное изображение или недопустимый тип изображения. Допустимые типы: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Недействительный основной навык",
        ],
        'url' => [
            'valid_media_path' => "Введите действительную ссылку на изображение",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Введите ссылку на действительный профиль linked in",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Выберите действительные документы учёта времени",
            'max' =>
                "Размер файла документа должен быть "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Вы не можете добавить время на даты в будущем",
        ],
        'news_image' => [
            'valid_media_path' => "Введите действительную медиа-картинку",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Введите действительную медиа-картинку",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Выберите действительный тип изображения",
            'max' =>
                "Размер изображения должен быть "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Введите действительную ссылку на видео",
            'max_video_url' => "Максимум ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "Максимум ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
        ],
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
        'page_details.slug' => "блок",
        'page_details.translations' => "переводы",
        'page_details.translations.*.lang' => "код языка",
        'page_details.translations.*.title' => "заголовок",
        'page_details.translations.*.sections' => "разделы",
        'translations.*.values' => "значения",
        'media_images.*.media_name' => "имя медиа",
        'media_images.*.media_type' => "тип медиа",
        'media_images.*.media_path' => "путь медиа",
        'media_videos.*.media_name' => "имя медиа",
        'media_videos.*.media_type' => "тип медиа",
        'media_videos.*.media_path' => "путь медиа",
        'documents.*.document_name' => "имя документа",
        'documents.*.document_type' => "тип документа",
        'documents.*.document_path' => "путь документа",
        'slider_detail.translations.*.lang' => "код языка",
        'skills.*.skill_id' => "id навыка",
        'location.city' => "город",
        'location.country' => "страна",
        'password_confirmation' => "подтвердите пароль",
        'translations.*.lang' => "код языка",
        'is_mandatory' => "обязательно",
		'page_details.translations.*.sections.*.title' => "заголовок",
		'page_details.translations.*.sections.*.description' => "описание",
		'location.city_id' => "город",
		'location.country_code' => "код страны",
		'organisation.organisation_id' => "id организации",
		'mission_detail.*.lang' => "код языка",
        'to_user_id' => "id пользователя",
        'custom_fields.*.field_id' => "id поля",
        'settings.*.tenant_setting_id' => "id настройки клиента",
        'settings.*.value' => "значение",
        'option_value.translations.*.lang' => "код языка",
        'timesheet_entries.*.timesheet_id' => "id учёта времени",
		'mission_detail.*.short_description' => "краткое описание",
        'news_content.translations' => "переводы",
        'news_content.translations.*.lang' => "код языка",
        'news_content.translations.*.title' => "заголовок",
        'news_content.translations.*.description' => "описание",
        'translations.*.title' => "заголовок",
        'settings.*.notification_type_id' => "id типа уведомления",
        'user_ids.*' => "id пользователя",
        'mission_detail.*.custom_information' => "пользовательская информация",
        'mission_detail.*.custom_information.*.title' => "заголовок",
        'mission_detail.*.custom_information.*.description' => "описание",
        'mission_detail.*.title' => "заголовок",
        'organisation.organisation_name' => "имя организации",
        'cities.*.translations.*.lang' => "код языка",
        'cities.*.translations.*.name' => "имя",
        'cities.*.translations' => "переводы",
        'media_images.*.sort_order' => "порядок сортировки",
        'media_videos.*.sort_order' => "порядок сортировки",
        'documents.*.sort_order' => "порядок сортировки",
        'countries.*.translations.*.lang' => "код языка",
        'countries.*.translations.*.name' => "имя",
        'countries.*.translations' => "переводы",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "код языка",
        'translations.*.name' => "имя",
        'translations' => "переводы",
        'mission_detail.*.section' => "раздел",
        'mission_detail.*.section.*.title' => "заголовок",
        'mission_detail.*.section.*.description' => "описание",
		],

];
?>
