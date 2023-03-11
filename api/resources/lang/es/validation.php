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

    'accepted'             => "El campo :attribute debe ser aceptado.",
    'active_url'           => "El campo :attribute no es una URL válida.",
    'after'                => "El campo :attribute debe ser una fecha posterior al :date.",
    'alpha'                => "El campo :attribute solo puede contener letras.",
    'alpha_dash'           => "El campo :attribute solo puede contener letras, números o guiones.",
    'alpha_num'            => "El campo :attribute solo puede contener letras y números.",
    'array'                => "El campo :attribute debe ser un array.",
    'before'               => "El campo :attribute debe ser una fecha anterior al :date.",
    'between'              => [
        'numeric' => "El campo :attribute debe estar entre :min y :max.",
        'file'    => "El campo :attribute debe estar entre :min y :max kilobytes.",
        'string'  => "El campo :attribute debe estar entre :min y :max caracteres.",
        'array'   => "El campo :attribute debe tener entre :min y :max artículos.",
    ],
    'boolean'              => "El campo :attribute debe ser verdadero o falso.",
    'confirmed'            => "La confirmación del campo :attribute no coincide.",
    'date'                 => "El campo :attribute no es una fecha válida.",
    'date_format'          => "El campo :attribute no coincide con el formato :format.",
    'different'            => "El campo :attribute y :other deben ser diferentes.",
    'digits'               => "El campo :attribute debe ser de :digits digits.",
    'digits_between'       => "El campo :attribute debe ser de entre :min y :max dígitos.",
    'email'                => "El campo :attribute debe ser una dirección de correo electrónico válida.",
    'filled'               => "El campo :attribute es obligatorio.",
    'exists'               => "El campo :attribute seleccionado es inválido.",
    'image'                => "El campo :attribute debe ser una imagen.",
    'in'                   => "El campo :attribute seleccionado es inválido.",
    'integer'              => "El campo :attribute debe ser un número entero.",
    'ip'                   => "El campo :attribute debe ser una dirección IP válida.",
    'max'                  => [
        'numeric' => "El campo :attribute no puede ser mayor que :max.",
        'file'    => "El campo :attribute no puede ser de más de :max kilobytes.",
        'string'  => "El campo :attribute no puede ser de más de :max caracteres.",
        'array'   => "El campo :attribute no puede tener más de :max artículos.",
    ],
    'mimes'                => "El campo :attribute debe ser un archivo de tipo: :values.",
    'mimetypes'            => "El campo :attribute debe ser un archivo de tipo: :values.",
    'min'                  => [
        'numeric' => "El campo :attribute debe ser de al menos :min.",
        'file'    => "El campo :attribute debe ser de al menos :min kilobytes.",
        'string'  => "El campo :attribute debe ser de al menos :min caracteres.",
        'array'   => "El campo :attribute debe tener al menos :min artículos.",
    ],
    'not_in'               => "El campo :attribute seleccionado es inválido.",
    'numeric'              => "El campo :attribute debe ser un número.",
    'regex'                => "El formato del campo :attribute es inválido.",
    'required'             => "El campo :attribute es obligatorio.",
    'required_if'          => "El campo :attribute es obligatorio cuando :other es :value.",
    'required_with'        => "El campo :attribute es obligatorio cuando :values está presente.",
    'required_with_all'    => "El campo :attribute es obligatorio cuando :values está presente.",
    'required_without'     => "El campo :attribute es obligatorio cuando :values no está presente.",
    'required_without_all' => "El campo :attribute es obligatorio cuando ninguno de los :values está presente.",
    'same'                 => "El campo :attribute y :other deben coincidir.",
    'size'                 => [
        'numeric' => "El tamaño de :attribute debe ser :size.",
        'file'    => "El tamaño de :attribute debe ser :size kilobytes.",
        'string'  => "El tamaño de :attribute debe ser :size caracteres.",
        'array'   => "El campo :attribute debe contener artículos :size.",
    ],
    'timezone'             => "El campo :attribute debe ser una zona válida.",
    'unique'               => "El campo :attribute ya se ha tomado.",
    'url'                  => "El formato del campo :attribute es inválido.",
    'present'              => "El campo :attribute es obligatorio",
	'distinct'             => "El campo :attribute tiene un valor duplicado.",

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
            'valid_media_path' => "Introduzca una imagen de medios válida",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Introduzca un archivo de documento válido",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Introduzca una URL de YouTube válida",
        ],
		'avatar' => [
            'valid_profile_image' => "Imagen inválida o formato de imagen no permitido Formatos permitidos: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Habilidad parental inválida",
        ],
        'url' => [
            'valid_media_path' => "Introduzca una URL de imagen válida",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Introduzca una URL de LinkedIn válida",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Seleccione una plantilla horaria válida",
            'max' =>
                "El tamaño del archivo debe ser de "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "No puede añadir una entrada de tiempo para fechas futuras",
        ],
        'news_image' => [
            'valid_media_path' => "Introduzca una imagen de medios válida",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Introduzca una imagen de medios válida",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Introduzca un tipo de imagen válido",
            'max' =>
                "El tamaño de la imagen debe ser "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Introduzca una URL de vídeo válida",
            'max_video_url' => "Máximo ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "Máximo ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
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
        'page_details.slug' => "título interno",
        'page_details.translations' => "traducciones",
        'page_details.translations.*.lang' => "código de idioma",
        'page_details.translations.*.title' => "título",
        'page_details.translations.*.sections' => "secciones",
        'translations.*.values' => "valores",
        'media_images.*.media_name' => "nombre de los medios",
        'media_images.*.media_type' => "tipo de los medios",
        'media_images.*.media_path' => "ruta de los medios",
        'media_videos.*.media_name' => "nombre de los medios",
        'media_videos.*.media_type' => "tipo de los medios",
        'media_videos.*.media_path' => "ruta de los medios",
        'documents.*.document_name' => "nombre del documento",
        'documents.*.document_type' => "tipo de documento",
        'documents.*.document_path' => "ruta del documento",
        'slider_detail.translations.*.lang' => "código de idioma",
        'skills.*.skill_id' => "ID de habilidad",
        'location.city' => "ciudad",
        'location.country' => "país",
        'password_confirmation' => "confirmar contraseña",
        'translations.*.lang' => "código de idioma",
        'is_mandatory' => "obligatorio",
		'page_details.translations.*.sections.*.title' => "título",
		'page_details.translations.*.sections.*.description' => "descripción",
		'location.city_id' => "ciudad",
		'location.country_code' => "código de país",
		'organisation.organisation_id' => "ID de la organización",
		'mission_detail.*.lang' => "código de idioma",
        'to_user_id' => "ID de usuario",
        'custom_fields.*.field_id' => "ID de campo",
        'settings.*.tenant_setting_id' => "ID de configuración de usuario",
        'settings.*.value' => "valor",
        'option_value.translations.*.lang' => "código de idioma",
        'timesheet_entries.*.timesheet_id' => "ID de plantilla horaria",
		'mission_detail.*.short_description' => "descripción breve",
        'news_content.translations' => "traducciones",
        'news_content.translations.*.lang' => "código de idioma",
        'news_content.translations.*.title' => "título",
        'news_content.translations.*.description' => "descripción",
        'translations.*.title' => "título",
        'settings.*.notification_type_id' => "ID del tipo de notificación",
        'user_ids.*' => "ID de usuario",
        'mission_detail.*.custom_information' => "información personalizada",
        'mission_detail.*.custom_information.*.title' => "título",
        'mission_detail.*.custom_information.*.description' => "descripción",
        'mission_detail.*.title' => "título",
        'organisation.organisation_name' => "nombre de la organización",
        'cities.*.translations.*.lang' => "código de idioma",
        'cities.*.translations.*.name' => "nombre",
        'cities.*.translations' => "traducciones",
        'media_images.*.sort_order' => "orden de clasificación",
        'media_videos.*.sort_order' => "orden de clasificación",
        'documents.*.sort_order' => "orden de clasificación",
        'countries.*.translations.*.lang' => "código de idioma",
        'countries.*.translations.*.name' => "nombre",
        'countries.*.translations' => "traducciones",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "código de idioma",
        'translations.*.name' => "nombre",
        'translations' => "traducciones",
        'mission_detail.*.section' => "sección",
        'mission_detail.*.section.*.title' => "título",
        'mission_detail.*.section.*.description' => "descripción",
		],

];
?>
