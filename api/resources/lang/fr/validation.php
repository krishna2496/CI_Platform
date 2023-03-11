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

    'accepted'             => ":attribute doit être accepté.",
    'active_url'           => ":attribute n’est pas une URL valide.",
    'after'                => ":attribute doit être une date après :date.",
    'alpha'                => ":attribute ne doit contenir que des lettres.",
    'alpha_dash'           => ":attributene ne doit contenir que des lettres, des chiffres et des tirets.",
    'alpha_num'            => ":attribute ne doit contenir que des lettres et des chiffres.",
    'array'                => ":attribute doit être un éventail.",
    'before'               => ":attribute doit être une date avant :date.",
    'between'              => [
        'numeric' => ":attribute doit être entre :min et :max.",
        'file'    => ":attribute doit être entre :min et :max kilo-octets.",
        'string'  => ":attribute doit être entre :min et :max caractères.",
        'array'   => ":attribute doit avoir entre :min et :max éléments.",
    ],
    'boolean'              => "Le champ :attribute doit être vrai ou faux.",
    'confirmed'            => "La confirmation :attribute ne correspond pas.",
    'date'                 => ":attribute n’est pas une date valide.",
    'date_format'          => ":attribute ne correspond pas au format :format.",
    'different'            => ":attribute et :other doivent être différents.",
    'digits'               => ":attribute doit être :digits chiffres.",
    'digits_between'       => ":attribute doit être entre :min et :max chiffres.",
    'email'                => ":attribute doit être une adresse e-mail valide.",
    'filled'               => "Le champ :attribute est requis.",
    'exists'               => ":attribute sélectionné est invalide.",
    'image'                => ":attribute doit être une image.",
    'in'                   => ":attribute sélectionné est invalide.",
    'integer'              => ":attribute doit être un entier.",
    'ip'                   => ":attribute doit être une adresse IP valide.",
    'max'                  => [
        'numeric' => ":attributene doit pas être supérieur à :max.",
        'file'    => ":attribute ne doit pas être supérieur à :max kilo-octets.",
        'string'  => ":attribute ne doit pas être supérieur à :max caractères.",
        'array'   => ":attribute ne doit pas avoir plus de :max éléments.",
    ],
    'mimes'                => ":attribute doit être un fichier de type :values.",
    'mimetypes'            => ":attribute doit être un fichier de type :values.",
    'min'                  => [
        'numeric' => ":attribute doit être au moins :min.",
        'file'    => ":attribute doit être au moins :min kilo-octets.",
        'string'  => ":attribute doit être au moins :min caractères.",
        'array'   => ":attribute doit avoir au moins :min éléments.",
    ],
    'not_in'               => ":attribute sélectionné est invalide.",
    'numeric'              => ":attribute doit être un chiffre.",
    'regex'                => "Le format :attribute est invalide.",
    'required'             => "Le champ :attribute est requis.",
    'required_if'          => "Le champ :attribute est nécessaire lorsque :other est :value.",
    'required_with'        => "Le champ :attribute est nécessaire lorsque :values est présent.",
    'required_with_all'    => "Le champ :attribute est nécessaire lorsque :values est présent.",
    'required_without'     => "Le champ :attribute est nécessaire lorsque :values n’est pas présent.",
    'required_without_all' => "Le champ :attribute est nécessaire lorsque aucune des :values n’est présente.",
    'same'                 => ":attribute et :other doivent correspondre.",
    'size'                 => [
        'numeric' => ":attribute doit être :size.",
        'file'    => ":attribute doit être :size kilo-octets.",
        'string'  => ":attribute doit être :size caractères.",
        'array'   => ":attribute doit contenir :size éléments.",
    ],
    'timezone'             => ":attribute doit être une zone valide.",
    'unique'               => ":attribute a déjà été pris.",
    'url'                  => "Le format :attribute est invalide.",
    'present'              => "Le champ :attribute est requis",
	'distinct'             => "Le champ :attribute a une valeur double.",

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
            'valid_media_path' => "Veuillez saisir une image média valide",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Veuillez saisir un fichier document valide",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Veuillez saisir une adresse youtube valide",
        ],
		'avatar' => [
            'valid_profile_image' => "Fichier image invalide ou type d’image non permise. Types permis : png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Compétence principale invalide",
        ],
        'url' => [
            'valid_media_path' => "Merci de saisir une url d’image valide",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Merci de sélectionner une url linkedIn valide",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Veuillez sélectionner un document de feuille de temps valide",
            'max' =>
                "La taille des dossiers des documents doit être "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Vous ne pouvez pas ajouter d’entrée de temps pour des dates futures",
        ],
        'news_image' => [
            'valid_media_path' => "Veuillez saisir une image média valide",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Veuillez saisir une image média valide",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Veuillez sélectionner un type d’image valide",
            'max' =>
                "La taille de l’image doit être "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Veuillez saisir une url vidéo valide",
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
        'page_details.slug' => "slug",
        'page_details.translations' => "traductions",
        'page_details.translations.*.lang' => "code langue",
        'page_details.translations.*.title' => "titre",
        'page_details.translations.*.sections' => "sections",
        'translations.*.values' => "valeurs",
        'media_images.*.media_name' => "nom du média",
        'media_images.*.media_type' => "type de média",
        'media_images.*.media_path' => "chemin d’accès du média",
        'media_videos.*.media_name' => "nom du média",
        'media_videos.*.media_type' => "type de média",
        'media_videos.*.media_path' => "chemin d’accès du média",
        'documents.*.document_name' => "nom du document",
        'documents.*.document_type' => "type de document",
        'documents.*.document_path' => "chemin d’accès du document",
        'slider_detail.translations.*.lang' => "code langue",
        'skills.*.skill_id' => "Identifiant de compétence",
        'location.city' => "ville",
        'location.country' => "pays",
        'password_confirmation' => "confirmer le mot de passe",
        'translations.*.lang' => "code langue",
        'is_mandatory' => "obligatoire",
		'page_details.translations.*.sections.*.title' => "titre",
		'page_details.translations.*.sections.*.description' => "description",
		'location.city_id' => "ville",
		'location.country_code' => "code du pays",
		'organisation.organisation_id' => "identifiant de l’organisme",
		'mission_detail.*.lang' => "code langue",
        'to_user_id' => "identifiant de l’utilisateur",
        'custom_fields.*.field_id' => "identifiant du secteur",
        'settings.*.tenant_setting_id' => "identifiant réglage locataire",
        'settings.*.value' => "valeur",
        'option_value.translations.*.lang' => "code langue",
        'timesheet_entries.*.timesheet_id' => "identifiant feuille de temps",
		'mission_detail.*.short_description' => "description brève",
        'news_content.translations' => "traductions",
        'news_content.translations.*.lang' => "code langue",
        'news_content.translations.*.title' => "titre",
        'news_content.translations.*.description' => "description",
        'translations.*.title' => "titre",
        'settings.*.notification_type_id' => "identifiant type notification",
        'user_ids.*' => "identifiant de l’utilisateur",
        'mission_detail.*.custom_information' => "information douane",
        'mission_detail.*.custom_information.*.title' => "titre",
        'mission_detail.*.custom_information.*.description' => "description",
        'mission_detail.*.title' => "titre",
        'organisation.organisation_name' => "nom de l’organisme",
        'cities.*.translations.*.lang' => "code langue",
        'cities.*.translations.*.name' => "nom",
        'cities.*.translations' => "traductions",
        'media_images.*.sort_order' => "ordre de tri",
        'media_videos.*.sort_order' => "ordre de tri",
        'documents.*.sort_order' => "ordre de tri",
        'countries.*.translations.*.lang' => "code langue",
        'countries.*.translations.*.name' => "nom",
        'countries.*.translations' => "traductions",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "code langue",
        'translations.*.name' => "nom",
        'translations' => "traductions",
        'mission_detail.*.section' => "section",
        'mission_detail.*.section.*.title' => "titre",
        'mission_detail.*.section.*.description' => "description",
        'states.*.translations.*.lang' => 'language code',
        'states.*.translations.*.name' => 'name',
        'states.*.translations' => 'translations',
        'mission_detail.*.label_goal_objective' => 'label goal objective',
        'mission_detail.*.label_goal_achieved' => 'label goal achieved',
		],

];
?>
