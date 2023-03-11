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

    'accepted'             => "Il :attribute dev’essere accettato.",
    'active_url'           => "Il :attribute non è un URL valido.",
    'after'                => "La :attribute dev’essere una data successiva al :date.",
    'alpha'                => "Il :attribute può contenere solo lettere.",
    'alpha_dash'           => "La :attribute può contenere solo lettere, numeri e trattini.",
    'alpha_num'            => "Il :attribute può contenere solo lettere e numeri.",
    'array'                => "Il :attribute dev’essere una lista.",
    'before'               => "La :attribute dev’essere una data precedente il :data.",
    'between'              => [
        'numeric' => "Il :attribute dev’essere compreso tra :min. e :max.",
        'file'    => "Il :attribute devono essere compreso tra :min. e :max. kilobyte.",
        'string'  => "La :attribute dev’essere compreso tra :min. e :max. caratteri.",
        'array'   => "La :attribute dev’essere contenere tra i :min. e i :max. elementi.",
    ],
    'boolean'              => "Il campo relativo a :attribute dev’essere vero o falso.",
    'confirmed'            => "L’:attribute confermato non corrisponde a quello precedente.",
    'date'                 => "Il :attribute non è una data valida.",
    'date_format'          => "Il formato dell’:attribute non corrisponde al formato :format.",
    'different'            => "L’:attribute e :other devono essere diversi.",
    'digits'               => "Il :attribute dev’essere composto dalle cifre :digits.",
    'digits_between'       => "Il :attribute dev’essere contenere dalle :min. alle :max. cifre.",
    'email'                => "L’ :attribute dev’essere un indirizzo di posta elettronica valido.",
    'filled'               => "Il campo :attribute è obbligatorio.",
    'exists'               => "L’ :attribute selezionato non è valido.",
    'image'                => "La :attribute dev’essere un’immagine.",
    'in'                   => "L’ :attribute selezionato non è valido.",
    'integer'              => "Il :attribute dev’essere un numero intero.",
    'ip'                   => "L’ :attribute dev’essere un indirizzo IP valido.",
    'max'                  => [
        'numeric' => "L’ :attribute non deve superare i :max.",
        'file'    => "L’ :attribute non deve superare i :max. kilobytes.",
        'string'  => "L’ :attribute non deve superare i :max. caratteri.",
        'array'   => "L’ :attribute può non contenere più di :max. elementi.",
    ],
    'mimes'                => "L’ :attribute dev’essere un file di tipo :values.",
    'mimetypes'            => "L’ :attribute dev’essere un file di tipo :values.",
    'min'                  => [
        'numeric' => "L’ :attribute dev’essere di almeno :min.",
        'file'    => "L’ :attribute dev’essere di almeno :min. kilobytes.",
        'string'  => "L’ :attribute dev’essere costituito da almeno :min. caratteri.",
        'array'   => "L’ :attribute dev’essere composto da almeno :min. elementi.",
    ],
    'not_in'               => "L’ :attribute selezionato non è valido.",
    'numeric'              => "Il :attribute dev’essere un numero.",
    'regex'                => "Il formato del :attribute non è valido.",
    'required'             => "Il campo :attribute è obbligatorio.",
    'required_if'          => "Il campo :attribute è obbligatorio quando :altro è :value.",
    'required_with'        => "Il campo :attribute è obbligatorio quando è presente :values.",
    'required_with_all'    => "Il campo :attribute è obbligatorio quando è presente :values.",
    'required_without'     => "Il campo :attribute è obbligatorio quando non è presenti :values.",
    'required_without_all' => "Il campo :attribute è obbligatorio quando nessuno dei :values è presente.",
    'same'                 => "L’ :attribute e :other devono essere diversi.",
    'size'                 => [
        'numeric' => "Il :attribute deve avere dimensioni di :size.",
        'file'    => "Il :attribute dev’essere di :size kilobytes.",
        'string'  => "Il :attribute deve contenere :size caratteri.",
        'array'   => "L’ :attribute deve contenere :size elementi.",
    ],
    'timezone'             => "Il :attribute dev’essere una zona valida.",
    'unique'               => "Il :attribute è già stato scelto da qualcun altro.",
    'url'                  => "Il formato del :attribute non è valido.",
    'present'              => "Il campo :attribute è obbligatorio",
	'distinct'             => "Il campo :attribute ha un valore doppio.",

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
            'valid_media_path' => "Ti preghiamo di inserire un’immagine valida",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Inserisci un file valido",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Inserisci un url youtube valido",
        ],
		'avatar' => [
            'valid_profile_image' => "File immagine non valido o formato non ammesso. Formati ammessi: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Competenza non valida",
        ],
        'url' => [
            'valid_media_path' => "Ti preghiamo di inserire un url dell’immagine valida",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Ti preghiamo di inserire un url linkedin valido",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Ti preghiamo d’inserire un documento relativo alla scheda orari valido",
            'max' =>
                "Le dimensioni del file del documento devono essere di "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Non puoi aggiungere le informazioni relative agli orari per date future",
        ],
        'news_image' => [
            'valid_media_path' => "Ti preghiamo di inserire un’immagine valida",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Ti preghiamo di inserire un’immagine valida",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Ti preghiamo di selezionare un tipo d’immagine valido",
            'max' =>
                "Le dimensioni dell’immagine devono essere di "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Ti preghiamo di inserire un url valido dell’immagine",
            'max_video_url' => "Massimo ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "Massimo ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
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
        'page_details.slug' => "chiocciola",
        'page_details.translations' => "traduzioni",
        'page_details.translations.*.lang' => "codice lingua",
        'page_details.translations.*.title' => "titolo",
        'page_details.translations.*.sections' => "sezioni",
        'translations.*.values' => "valori",
        'media_images.*.media_name' => "nome del file multimediale",
        'media_images.*.media_type' => "tipo di file multimediale",
        'media_images.*.media_path' => "percorso del file multimediale",
        'media_videos.*.media_name' => "nome del file multimediale",
        'media_videos.*.media_type' => "tipo di file multimediale",
        'media_videos.*.media_path' => "percorso del file multimediale",
        'documents.*.document_name' => "nome del documento",
        'documents.*.document_type' => "tipo di documento",
        'documents.*.document_path' => "percorso del documento",
        'slider_detail.translations.*.lang' => "codice lingua",
        'skills.*.skill_id' => "id competenza",
        'location.city' => "città",
        'location.country' => "paese",
        'password_confirmation' => "conferma password",
        'translations.*.lang' => "codice lingua",
        'is_mandatory' => "obbligatorio",
		'page_details.translations.*.sections.*.title' => "titolo",
		'page_details.translations.*.sections.*.description' => "descrizione",
		'location.city_id' => "città",
		'location.country_code' => "codice paese",
		'organisation.organisation_id' => "id organizzazione",
		'mission_detail.*.lang' => "codice lingua",
        'to_user_id' => "id utente",
        'custom_fields.*.field_id' => "id campo",
        'settings.*.tenant_setting_id' => "id impostazioni utente",
        'settings.*.value' => "valore",
        'option_value.translations.*.lang' => "codice lingua",
        'timesheet_entries.*.timesheet_id' => "id scheda oraria",
		'mission_detail.*.short_description' => "breve descrizione",
        'news_content.translations' => "traduzioni",
        'news_content.translations.*.lang' => "codice lingua",
        'news_content.translations.*.title' => "titolo",
        'news_content.translations.*.description' => "descrizione",
        'translations.*.title' => "titolo",
        'settings.*.notification_type_id' => "id del tipo di notifica",
        'user_ids.*' => "id utente",
        'mission_detail.*.custom_information' => "personalizza le informazioni",
        'mission_detail.*.custom_information.*.title' => "titolo",
        'mission_detail.*.custom_information.*.description' => "descrizione",
        'mission_detail.*.title' => "titolo",
        'organisation.organisation_name' => "nome dell’organizzazione",
        'cities.*.translations.*.lang' => "codice lingua",
        'cities.*.translations.*.name' => "nome",
        'cities.*.translations' => "traduzioni",
        'media_images.*.sort_order' => "ordinamento",
        'media_videos.*.sort_order' => "ordinamento",
        'documents.*.sort_order' => "ordinamento",
        'countries.*.translations.*.lang' => "codice lingua",
        'countries.*.translations.*.name' => "nome",
        'countries.*.translations' => "traduzioni",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "codice lingua",
        'translations.*.name' => "nome",
        'translations' => "traduzioni",
        'mission_detail.*.section' => "sezione",
        'mission_detail.*.section.*.title' => "titolo",
        'mission_detail.*.section.*.description' => "descrizione",
		],

];
?>
