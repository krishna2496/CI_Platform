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

    'accepted'             => "O :attribute deve ser aceito.",
    'active_url'           => "O :attribute não é uma URL válida.",
    'after'                => "O :attribute deve ser uma data após :date.",
    'alpha'                => "O :attribute deve conter apenas letras.",
    'alpha_dash'           => "O :attribute deve conter apenas letras, números e traços.",
    'alpha_num'            => "O :attribute deve conter apenas letras e números.",
    'array'                => "O :attribute deve ser uma série.",
    'before'               => "O :attribute deve ser uma data antes de :date.",
    'between'              => [
        'numeric' => "O :attribute deve estar entre :min e :max.",
        'file'    => "O :attribute deve estar entre :min e :max KB.",
        'string'  => "O :attribute deve estar entre :min e :max caracteres.",
        'array'   => "O :attribute deve estar entre :min e :max itens.",
    ],
    'boolean'              => "O campo de :attribute deve ser verdadeiro ou falso.",
    'confirmed'            => "A confirmação de :attribute não coincide.",
    'date'                 => "O :attribute não é uma data válida.",
    'date_format'          => "O :attribute não coincide com o formato :format.",
    'different'            => "O :attribute e :other devem ser diferentes.",
    'digits'               => "O :attribute deve ser :digits dígitos.",
    'digits_between'       => "O :attribute deve estar entre :min e :max dígitos.",
    'email'                => "O :attribute deve ser um endereço de e-mail válido.",
    'filled'               => "O campo :attribute é obrigatório.",
    'exists'               => "O :attribute selecionado não é válido.",
    'image'                => "O :attribute deve ser uma imagem.",
    'in'                   => "O :attribute selecionado não é válido.",
    'integer'              => "O :attribute deve ser um inteiro.",
    'ip'                   => "O :attribute deve ser um endereço IP válido.",
    'max'                  => [
        'numeric' => "O :attribute não deve ser superior a :max.",
        'file'    => "O :attribute não deve ser superior a :max KB. ",
        'string'  => "O :attribute não deve ser superior a :max caracteres.",
        'array'   => "O :attribute não deve ter mais do que :max itens.",
    ],
    'mimes'                => "O :attribute deve ser um arquivo do tipo :values.",
    'mimetypes'            => "O :attribute deve ser um arquivo do tipo :values.",
    'min'                  => [
        'numeric' => "O :attribute deve ter no mínimo :min.",
        'file'    => "O :attribute deve ter no mínimo :min KB.",
        'string'  => "O :attribute deve ter no mínimo :min caracteres.",
        'array'   => "O :attribute deve ter no mínimo :min itens.",
    ],
    'not_in'               => "O :attribute selecionado não é válido.",
    'numeric'              => "O :attribute deve ser um número.",
    'regex'                => "O formato de :attribute não é válido.",
    'required'             => "O campo :attribute é obrigatório.",
    'required_if'          => "O campo :attribute é obrigatório quando :other é :value.",
    'required_with'        => "O campo :attribute é obrigatório quando :value está presente.",
    'required_with_all'    => "O campo :attribute é obrigatório quando :value está presente.",
    'required_without'     => "O campo :attribute é obrigatório quando :value não está presente.",
    'required_without_all' => "O campo :attribute é obrigatório quando nenhum de :values estão presentes.",
    'same'                 => "O :attribute e :other devem coincidir.",
    'size'                 => [
        'numeric' => "O :attribute deve ser :size.",
        'file'    => "O :attribute deve ter :size KB.",
        'string'  => "O :attribute deve ter :size caracteres.",
        'array'   => "O :attribute deve conter :size itens.",
    ],
    'timezone'             => "O :attribute deve ser uma zona válida.",
    'unique'               => "O :attribute já foi usado.",
    'url'                  => "O formato de :attribute não é válido.",
    'present'              => "O campo :attribute é obrigatório",
	'distinct'             => "O campo :attribute tem um valor duplicado.",

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
            'valid_media_path' => "Por favor, insira uma imagem de mídia válida",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Por favor, insira um arquivo de documento válido",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Por favor, insira uma URL de Youtube válida",
        ],
		'avatar' => [
            'valid_profile_image' => "Arquivo de imagem inválido ou o tipo de imagem não é permitido. Tipos permitidos: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Habilidade parental inválida",
        ],
        'url' => [
            'valid_media_path' => "Por favor, insira uma URL de imagem válida",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Por favor, insira uma URL de LinkedIn válida",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Por favor, selecione um documento de tabela de tempos válido",
            'max' =>
                "O tamanho do arquivo de documento deve ser "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Você não pode adicionar a entrada de tempo para datas futuras",
        ],
        'news_image' => [
            'valid_media_path' => "Por favor, insira uma imagem de mídia válida",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Por favor, insira uma imagem de mídia válida",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Por favor, selecione um tipo de imagem válido",
            'max' =>
                "O tamanho de imagem deve ser "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Por favor, insira uma URL de vídeo válida",
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
        'page_details.slug' => "slug",
        'page_details.translations' => "traduções",
        'page_details.translations.*.lang' => "código de idioma",
        'page_details.translations.*.title' => "título",
        'page_details.translations.*.sections' => "seções",
        'translations.*.values' => "valores",
        'media_images.*.media_name' => "nome da mídia",
        'media_images.*.media_type' => "tipo de mídia",
        'media_images.*.media_path' => "caminho da mídia",
        'media_videos.*.media_name' => "nome da mídia",
        'media_videos.*.media_type' => "tipo de mídia",
        'media_videos.*.media_path' => "caminho da mídia",
        'documents.*.document_name' => "nome do documento",
        'documents.*.document_type' => "tipo de documento",
        'documents.*.document_path' => "caminho do documento",
        'slider_detail.translations.*.lang' => "código de idioma",
        'skills.*.skill_id' => "ID da habilidade",
        'location.city' => "cidade",
        'location.country' => "país",
        'password_confirmation' => "confirmar senha",
        'translations.*.lang' => "código de idioma",
        'is_mandatory' => "obrigatório",
		'page_details.translations.*.sections.*.title' => "título",
		'page_details.translations.*.sections.*.description' => "Descrição",
		'location.city_id' => "cidade",
		'location.country_code' => "código do país",
		'organisation.organisation_id' => "ID da organização",
		'mission_detail.*.lang' => "código de idioma",
        'to_user_id' => "ID do usuário",
        'custom_fields.*.field_id' => "ID do campo",
        'settings.*.tenant_setting_id' => "ID da configuração do inquilino",
        'settings.*.value' => "valor",
        'option_value.translations.*.lang' => "código de idioma",
        'timesheet_entries.*.timesheet_id' => "ID da tabela de tempos",
		'mission_detail.*.short_description' => "Descrição breve",
        'news_content.translations' => "traduções",
        'news_content.translations.*.lang' => "código de idioma",
        'news_content.translations.*.title' => "título",
        'news_content.translations.*.description' => "Descrição",
        'translations.*.title' => "título",
        'settings.*.notification_type_id' => "ID do tipo de notificação",
        'user_ids.*' => "ID do usuário",
        'mission_detail.*.custom_information' => "personalizar informações",
        'mission_detail.*.custom_information.*.title' => "título",
        'mission_detail.*.custom_information.*.description' => "Descrição",
        'mission_detail.*.title' => "título",
        'organisation.organisation_name' => "nome da organização",
        'cities.*.translations.*.lang' => "código de idioma",
        'cities.*.translations.*.name' => "nome",
        'cities.*.translations' => "traduções",
        'media_images.*.sort_order' => "ordem de classificação",
        'media_videos.*.sort_order' => "ordem de classificação",
        'documents.*.sort_order' => "ordem de classificação",
        'countries.*.translations.*.lang' => "código de idioma",
        'countries.*.translations.*.name' => "nome",
        'countries.*.translations' => "traduções",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "código de idioma",
        'translations.*.name' => "nome",
        'translations' => "traduções",
        'mission_detail.*.section' => "seção",
        'mission_detail.*.section.*.title' => "título",
        'mission_detail.*.section.*.description' => "Descrição",
		],

];
?>
