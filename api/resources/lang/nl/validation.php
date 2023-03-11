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

    'accepted'             => "Het :attribute moet worden geaccepteerd.",
    'active_url'           => "Het :attribute is geen geldige URL.",
    'after'                => "Het :attribute moet een datum zijn die valt na :date.",
    'alpha'                => "Het :attribute mag alleen letters bevatten.",
    'alpha_dash'           => "Het :attribute mag alleen letters, cijfers en koppelstreepjes bevatten.",
    'alpha_num'            => "Het :attribute mag alleen letters en cijfers bevatten.",
    'array'                => "Het :attribute moet een reeks zijn.",
    'before'               => "Het :attribute moet een datum zijn die valt voor :date.",
    'between'              => [
        'numeric' => "Het :attribute moet tussen :min en :max zijn.",
        'file'    => "Het :attribute moet tussen :min en :max kilobytes zijn.",
        'string'  => "Het :attribute moet tussen :min en :max tekens zijn.",
        'array'   => "Het :attribute moet tussen :min en :max items bevatten.",
    ],
    'boolean'              => "Het veld :attribute moet waar of niet waar zijn.",
    'confirmed'            => "De bevestiging van :attribute komt niet overeen.",
    'date'                 => "Het :attribute is geen geldige datum.",
    'date_format'          => "Het :attribute komt niet overeen met het format :format.",
    'different'            => "Het :attribute en :other moeten verschillend zijn.",
    'digits'               => "Het :attribute moet :digits cijfers zijn.",
    'digits_between'       => "Het :attribute moet tussen :min en :max cijfers zijn.",
    'email'                => "Het :attribute moet een geldig e-mailadres zijn.",
    'filled'               => "Het veld :attribute is verplicht.",
    'exists'               => "Het geselecteerde :attribute is ongeldig.",
    'image'                => "Het :attribute moet een afbeelding zijn.",
    'in'                   => "Het geselecteerde :attribute is ongeldig.",
    'integer'              => "Het :attribute moet een heel getal zijn.",
    'ip'                   => "Het :attribute moet een geldig IP-adres zijn.",
    'max'                  => [
        'numeric' => "Het :attribute mag niet groter zijn dan :max.",
        'file'    => "Het :attribute mag niet groter zijn dan :max kilobytes.",
        'string'  => "Het :attribute mag niet langer zijn dan :max tekens.",
        'array'   => "Het :attribute mag niet meer dan :max items bevatten.",
    ],
    'mimes'                => "Het :attribute moet een bestand van het type :values zijn.",
    'mimetypes'            => "Het :attribute moet een bestand van het type :values zijn.",
    'min'                  => [
        'numeric' => "Het :attribute moet tenminste :min zijn.",
        'file'    => "Het :attribute moet tenminste :min kilobytes zijn.",
        'string'  => "Het :attribute moet tenminste :min tekens zijn.",
        'array'   => "Het :attribute moet tenminste :min items bevatten.",
    ],
    'not_in'               => "Het geselecteerde :attribute is ongeldig.",
    'numeric'              => "Het :attribute moet een cijfer zijn.",
    'regex'                => "De indeling van :attribute is ongeldig.",
    'required'             => "Het veld :attribute is verplicht.",
    'required_if'          => "Het veld :attribute is verplicht als :other :value is.",
    'required_with'        => "Het veld :attribute is verplicht als :values aanwezig is.",
    'required_with_all'    => "Het veld :attribute is verplicht als :values aanwezig is.",
    'required_without'     => "Het veld :attribute is verplicht als :values niet aanwezig is.",
    'required_without_all' => "Het veld :attribute is verplicht als geen van :values aanwezig zijn.",
    'same'                 => "Het :attribute en :other moeten hetzelfde zijn.",
    'size'                 => [
        'numeric' => "Het :attribute moet :size zijn.",
        'file'    => "Het :attribute moet :size kilobytes zijn.",
        'string'  => "Het :attribute moet :size tekens zijn.",
        'array'   => "Het :attribute moet :size items bevatten.",
    ],
    'timezone'             => "Het :attribute moet een geldige zone zijn.",
    'unique'               => "Het :attribute is al bezet.",
    'url'                  => "De indeling van :attribute is ongeldig.",
    'present'              => "Het veld :attribute is verplicht",
	'distinct'             => "Het veld :attribute bevat een gedupliceerde waarde.",

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
            'valid_media_path' => "Voer een geldige media-afbeelding in",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Voer een geldig documentbestand in",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Voer een geldige YouTube-URL in",
        ],
		'avatar' => [
            'valid_profile_image' => "Ongeldig afbeeldingsbestand of type afbeelding is niet toegestaan. Toegestane types: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Ongeldige bovenliggende vaardigheid",
        ],
        'url' => [
            'valid_media_path' => "Voer een geldige afbeelding-URL in",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Voer een geldige LinkedIn URL in",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Selecteer een geldig urenstaatdocument",
            'max' =>
                "Bestandsgrootte van document moet zijn "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Je kunt geen tijdsregistratie voor toekomstige datums invoeren",
        ],
        'news_image' => [
            'valid_media_path' => "Voer een geldige media-afbeelding in",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Voer een geldige media-afbeelding in",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Selecteer een geldig afbeeldingstype",
            'max' =>
                "Afbeeldingsformaat moet zijn "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Voer een geldige video-URL in",
            'max_video_url' => "Maximum ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "Maximum ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
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
        'page_details.slug' => "kogel",
        'page_details.translations' => "vertalingen",
        'page_details.translations.*.lang' => "taalcode",
        'page_details.translations.*.title' => "titel",
        'page_details.translations.*.sections' => "secties",
        'translations.*.values' => "waarden",
        'media_images.*.media_name' => "medianamen",
        'media_images.*.media_type' => "mediatype",
        'media_images.*.media_path' => "mediapad",
        'media_videos.*.media_name' => "medianamen",
        'media_videos.*.media_type' => "mediatype",
        'media_videos.*.media_path' => "mediapad",
        'documents.*.document_name' => "documentnaam",
        'documents.*.document_type' => "documenttype",
        'documents.*.document_path' => "documentpad",
        'slider_detail.translations.*.lang' => "taalcode",
        'skills.*.skill_id' => "vaardigheid ID",
        'location.city' => "stad",
        'location.country' => "land",
        'password_confirmation' => "bevestig wachtwoord",
        'translations.*.lang' => "taalcode",
        'is_mandatory' => "verplicht",
		'page_details.translations.*.sections.*.title' => "titel",
		'page_details.translations.*.sections.*.description' => "beschrijving",
		'location.city_id' => "stad",
		'location.country_code' => "landcode",
		'organisation.organisation_id' => "organisatie ID",
		'mission_detail.*.lang' => "taalcode",
        'to_user_id' => "gebruiker ID",
        'custom_fields.*.field_id' => "veld ID",
        'settings.*.tenant_setting_id' => "huurdersinstelling ID",
        'settings.*.value' => "waarde",
        'option_value.translations.*.lang' => "taalcode",
        'timesheet_entries.*.timesheet_id' => "urenstaat ID",
		'mission_detail.*.short_description' => "korte beschrijving",
        'news_content.translations' => "vertalingen",
        'news_content.translations.*.lang' => "taalcode",
        'news_content.translations.*.title' => "titel",
        'news_content.translations.*.description' => "beschrijving",
        'translations.*.title' => "titel",
        'settings.*.notification_type_id' => "meldingstype ID",
        'user_ids.*' => "gebruiker ID",
        'mission_detail.*.custom_information' => "aangepaste gegevens",
        'mission_detail.*.custom_information.*.title' => "titel",
        'mission_detail.*.custom_information.*.description' => "beschrijving",
        'mission_detail.*.title' => "titel",
        'organisation.organisation_name' => "organisatienaam",
        'cities.*.translations.*.lang' => "taalcode",
        'cities.*.translations.*.name' => "naam",
        'cities.*.translations' => "vertalingen",
        'media_images.*.sort_order' => "sorteervolgorde",
        'media_videos.*.sort_order' => "sorteervolgorde",
        'documents.*.sort_order' => "sorteervolgorde",
        'countries.*.translations.*.lang' => "taalcode",
        'countries.*.translations.*.name' => "naam",
        'countries.*.translations' => "vertalingen",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "taalcode",
        'translations.*.name' => "naam",
        'translations' => "vertalingen",
        'mission_detail.*.section' => "sectie",
        'mission_detail.*.section.*.title' => "titel",
        'mission_detail.*.section.*.description' => "beschrijving",
		],

];
?>
