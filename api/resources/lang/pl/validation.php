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

    'accepted'             => "Należy zaakceptować :attribute.",
    'active_url'           => ":attribute nie jest prawidłowym adresem URL.",
    'after'                => ":attribute musi być datą po :date.",
    'alpha'                => ":attribute może zawierać tylko litery.",
    'alpha_dash'           => ":attribute może zawierać tylko litery, cyfry i myślniki.",
    'alpha_num'            => ":attribute może zawierać tylko litery i cyfry.",
    'array'                => ":attribute musi być szeregiem.",
    'before'               => ":attribute musi być datą przed :date.",
    'between'              => [
        'numeric' => ":attribute musi mieć od :min do :max.",
        'file'    => ":attribute musi mieć od :min do :max kilobajtów.",
        'string'  => ":attribute musi mieć od :min do :max znaków.",
        'array'   => ":attribute musi mieć od :min do :max elementów.",
    ],
    'boolean'              => "Pole :attribute musi być prawdziwe lub fałszywe.",
    'confirmed'            => "Potwierdzenie :attribute nie pasuje.",
    'date'                 => ":attribute nie jest poprawną datą.",
    'date_format'          => ":attribute nie pasuje do formatu :format.",
    'different'            => ":attribute i :other muszą być różne.",
    'digits'               => ":attribute musi mieć :digits cyfr.",
    'digits_between'       => ":attribute musi mieć od :min do :max cyfr.",
    'email'                => ":attribute musi być ważnym adresem e-mail.",
    'filled'               => "Pole :attribute jest wymagane.",
    'exists'               => "Wybrany :attribute jest nieprawidłowy.",
    'image'                => ":attribute musi być obrazem.",
    'in'                   => "Wybrany :attribute jest nieprawidłowy.",
    'integer'              => ":attribute musi być liczbą całkowitą.",
    'ip'                   => ":attribute musi być ważnym adresem IP.",
    'max'                  => [
        'numeric' => ":attribute nie może być większy niż :max.",
        'file'    => ":attribute nie może być większy niż :max. kilobajtów.",
        'string'  => ":attribute nie może mieć więcej niż :max znaków.",
        'array'   => ":attribute nie może mieć więcej niż :max elementów.",
    ],
    'mimes'                => ":attribute musi być plikiem typu: :values.",
    'mimetypes'            => ":attribute musi być plikiem typu: :values.",
    'min'                  => [
        'numeric' => ":attribute musi mieć co najmniej :min.",
        'file'    => ":attribute musi mieć co najmniej :min kilobajtów.",
        'string'  => ":attribute musi mieć co najmniej :min znaków.",
        'array'   => ":attribute musi mieć co najmniej :min elementów.",
    ],
    'not_in'               => "Wybrany :attribute jest nieprawidłowy.",
    'numeric'              => ":attribute musi być liczbą.",
    'regex'                => "Format :attribute jest nieprawidłowy.",
    'required'             => "Pole :attribute jest wymagane.",
    'required_if'          => "Pole :attribute jest wymagane, gdy :other wynosi :value.",
    'required_with'        => "Pole :attribute jest wymagane, gdy występuje :values.",
    'required_with_all'    => "Pole :attribute jest wymagane, gdy występuje :values.",
    'required_without'     => "Pole :attribute jest wymagane, gdy nie występuje :values.",
    'required_without_all' => "Pole :attribute jest wymagane, gdy nie występują żadne :values.",
    'same'                 => ":attribute i :other muszą się zgadzać.",
    'size'                 => [
        'numeric' => ":attribute musi mieć :size.",
        'file'    => ":attribute musi mieć :size kilobajtów.",
        'string'  => ":attribute musi mieć :size znaków.",
        'array'   => ":attribute musi zawierać :size elementów.",
    ],
    'timezone'             => ":attribute musi być ważną strefą.",
    'unique'               => ":attribute jest już zajęty.",
    'url'                  => "Format :attribute jest nieprawidłowy.",
    'present'              => "Pole :attribute jest wymagane",
	'distinct'             => "Pole :attribute ma zduplikowaną wartość.",

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
            'valid_media_path' => "Proszę wprowadzić prawidłowy obraz medialny ",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Proszę wprowadzić prawidłowy plik dokumentu",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Proszę wprowadzić ważny adres url youtube",
        ],
		'avatar' => [
            'valid_profile_image' => "Nieprawidłowy plik lub typ obrazu nie jest dozwolony. Dozwolone typy plików: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Nieprawidłowa umiejętność nadrzędna",
        ],
        'url' => [
            'valid_media_path' => "Proszę wprowadzić prawidłowy adres url obrazu",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Proszę wprowadzić prawidłowy adres url linkedIn",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Proszę wybrać prawidłową kartę czasu pracy",
            'max' =>
                "Rozmiar pliku dokumentu musi być "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Nie można dodawać wpisów czasowych dla przyszłych dat",
        ],
        'news_image' => [
            'valid_media_path' => "Proszę wprowadzić prawidłowy obraz medialny ",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Proszę wprowadzić prawidłowy obraz medialny ",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Proszę wybrać prawidłowy typ obrazu",
            'max' =>
                "Rozmiar obrazu musi być "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Proszę wprowadzić prawidłowy adres url wideo",
            'max_video_url' => "Maksimum ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "Maksimum ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
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
        'page_details.slug' => "kod generujący numer strony podczas wydruku",
        'page_details.translations' => "tłumaczenia",
        'page_details.translations.*.lang' => "kod języka",
        'page_details.translations.*.title' => "tytuł",
        'page_details.translations.*.sections' => "sekcje",
        'translations.*.values' => "wartości",
        'media_images.*.media_name' => "nazwa nośnika",
        'media_images.*.media_type' => "rodzaj nośnika",
        'media_images.*.media_path' => "ścieżka nośnika",
        'media_videos.*.media_name' => "nazwa nośnika",
        'media_videos.*.media_type' => "rodzaj nośnika",
        'media_videos.*.media_path' => "ścieżka nośnika",
        'documents.*.document_name' => "nazwa dokumentu",
        'documents.*.document_type' => "rodzaj dokumentu",
        'documents.*.document_path' => "ścieżka dokumentu",
        'slider_detail.translations.*.lang' => "kod języka",
        'skills.*.skill_id' => "identyfikator umiejętności",
        'location.city' => "miasto",
        'location.country' => "kraj",
        'password_confirmation' => "potwierdź hasło",
        'translations.*.lang' => "kod języka",
        'is_mandatory' => "obowiązkowe",
		'page_details.translations.*.sections.*.title' => "tytuł",
		'page_details.translations.*.sections.*.description' => "opis",
		'location.city_id' => "miasto",
		'location.country_code' => "kod kraju",
		'organisation.organisation_id' => "identyfikator organizacji",
		'mission_detail.*.lang' => "kod języka",
        'to_user_id' => "identyfikator użytkownika",
        'custom_fields.*.field_id' => "identyfikator pola",
        'settings.*.tenant_setting_id' => "identyfikacja najemcy",
        'settings.*.value' => "wartość",
        'option_value.translations.*.lang' => "kod języka",
        'timesheet_entries.*.timesheet_id' => "identyfikator karty czasu pracy",
		'mission_detail.*.short_description' => "krótki opis",
        'news_content.translations' => "tłumaczenia",
        'news_content.translations.*.lang' => "kod języka",
        'news_content.translations.*.title' => "tytuł",
        'news_content.translations.*.description' => "opis",
        'translations.*.title' => "tytuł",
        'settings.*.notification_type_id' => "identyfikator typu zgłoszenia",
        'user_ids.*' => "identyfikator użytkownika",
        'mission_detail.*.custom_information' => "niestandardowe informacje",
        'mission_detail.*.custom_information.*.title' => "tytuł",
        'mission_detail.*.custom_information.*.description' => "opis",
        'mission_detail.*.title' => "tytuł",
        'organisation.organisation_name' => "nazwa organizacji",
        'cities.*.translations.*.lang' => "kod języka",
        'cities.*.translations.*.name' => "imię",
        'cities.*.translations' => "tłumaczenia",
        'media_images.*.sort_order' => "kolejność sortowania",
        'media_videos.*.sort_order' => "kolejność sortowania",
        'documents.*.sort_order' => "kolejność sortowania",
        'countries.*.translations.*.lang' => "kod języka",
        'countries.*.translations.*.name' => "imię",
        'countries.*.translations' => "tłumaczenia",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "kod języka",
        'translations.*.name' => "imię",
        'translations' => "tłumaczenia",
        'mission_detail.*.section' => "sekcja",
        'mission_detail.*.section.*.title' => "tytuł",
        'mission_detail.*.section.*.description' => "opis",
		],

];
?>
