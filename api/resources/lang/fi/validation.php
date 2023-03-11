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

    'accepted'             => ":attribute tulee hyväksyä.",
    'active_url'           => ":attribute ei ole toimiva verkko-osoite.",
    'after'                => ":attribute tulee olla :date jälkeinen päivämäärä.",
    'alpha'                => ":attribute saa sisältää vain kirjaimia.",
    'alpha_dash'           => ":attribute saa sisältää vain kirjaimia, numeroita ja viivoja.",
    'alpha_num'            => ":attribute saa sisältää vain kirjaimia ja numeroita.",
    'array'                => ":attribute tulee olla taulukko.",
    'before'               => ":attribute tulee olla :date edeltävä päivämäärä.",
    'between'              => [
        'numeric' => ":attribute tulee olla :min ja :max väliltä.",
        'file'    => ":attribute tulee olla :min ja :max kilotavun väliltä.",
        'string'  => ":attribute tulee olla :min ja :max merkin väliltä.",
        'array'   => ":attribute tulee olla kohteita :min ja :max väliltä.",
    ],
    'boolean'              => ":attribute -kentän tulee olla tosi tai epätosi.",
    'confirmed'            => ":attribute -vahvistus ei omaa vastaavuutta.",
    'date'                 => ":attribute ei ole oikea päivämäärä.",
    'date_format'          => ":attribute ei vastaa muotoa :format.",
    'different'            => ":attribute ja :other tulee olla erilaiset.",
    'digits'               => ":attribute tulee olla :digits merkkiä.",
    'digits_between'       => ":attribute tulee olla :min ja :max merkin väliltä.",
    'email'                => ":attribute tulee olla toimiva sähköpostiosoite.",
    'filled'               => ":attribute -kenttä on pakollinen.",
    'exists'               => "Valittu :attribute on virheellinen.",
    'image'                => ":attribute tulee olla kuva.",
    'in'                   => "Valittu :attribute on virheellinen.",
    'integer'              => ":attribute tulee olla kokonaisluku.",
    'ip'                   => ":attribute tulee olla toimiva IP-osoite.",
    'max'                  => [
        'numeric' => ":attribute ei saa olla suurempi kuin :max.",
        'file'    => ":attribute ei saa olla suurempi kuin :max kilotavua.",
        'string'  => ":attribute ei saa olla suurempi kuin :max merkkiä.",
        'array'   => ":attribute ei saa sisältää enempää kuin :max kohdetta.",
    ],
    'mimes'                => ":attribute olla tiedostotyypiltään seuraava: :values.",
    'mimetypes'            => ":attribute olla tiedostotyypiltään seuraava: :values.",
    'min'                  => [
        'numeric' => ":attribute tulee olla vähintään :min.",
        'file'    => ":attribute tulee olla vähintään :min kilotavua.",
        'string'  => ":attribute tulee olla vähintään :min merkkiä.",
        'array'   => ":attribute tulee olla vähintään :min kohdetta.",
    ],
    'not_in'               => "Valittu :attribute on virheellinen.",
    'numeric'              => ":attribute tulee olla numero.",
    'regex'                => ":attribute -muoto on vihreellinen.",
    'required'             => ":attribute -kenttä on pakollinen.",
    'required_if'          => ":attribute -kenttä on pakollinen, mikäli :other on :value.",
    'required_with'        => ":attribute -kenttä on pakollinen, mikäli :values on läsnä.",
    'required_with_all'    => ":attribute -kenttä on pakollinen, mikäli :values on läsnä.",
    'required_without'     => ":attribute -kenttä on pakollinen, mikäli :values ei ole läsnä.",
    'required_without_all' => ":attribute -kenttä on pakollinen, mikäli yksikään :values ei ole läsnä.",
    'same'                 => ":attribute ja :other tulee vastata toisiaan.",
    'size'                 => [
        'numeric' => ":attribute tulee olla :size.",
        'file'    => ":attribute tulee olla :size kilotavua.",
        'string'  => ":attribute tulee olla :size merkkiä.",
        'array'   => ":attribute tulee sisältää :size kohdetta.",
    ],
    'timezone'             => ":attribute tulee olla toimiva alue.",
    'unique'               => ":attribute on jo käytetty.",
    'url'                  => ":attribute -muoto on vihreellinen.",
    'present'              => ":attribute -kenttä on pakollinen",
	'distinct'             => ":attribute -kentässä on kaksoiskappalearvo.",

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
            'valid_media_path' => "Anna kelvollinen mediakuva",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "Anna kelvollinen asiakirjatiedosto",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "Anna kelvollinen youtube-osoite",
        ],
		'avatar' => [
            'valid_profile_image' => "Virheellinen kuvatiedosto tai kuvatyyppiä ei ole sallittu. Sallitut tyypit: png, jpeg, jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "Virheellinen vanhemmuustaito",
        ],
        'url' => [
            'valid_media_path' => "Anna kelvollinen osoite kuvalle",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "Anna kelvollinen linkedIn-osoite",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "Valitse kelvollinen tuntiraporttiasiakirja",
            'max' =>
                "Asiakirjatiedoston koon tulee olla "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "Et voi lisätä aikamerkintää tuleville päivämäärille",
        ],
        'news_image' => [
            'valid_media_path' => "Anna kelvollinen mediakuva",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "Anna kelvollinen mediakuva",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "Valitse kelvollinen kuvatyyppi",
            'max' =>
                "Kuvan koon tulee olla "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "Anna kelvollinen osoite videolle",
            'max_video_url' => "Enintään ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "Enintään ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
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
        'page_details.slug' => "slugi",
        'page_details.translations' => "käännökset",
        'page_details.translations.*.lang' => "kielen koodi",
        'page_details.translations.*.title' => "otsikko",
        'page_details.translations.*.sections' => "osiot",
        'translations.*.values' => "arvot",
        'media_images.*.media_name' => "median nimi",
        'media_images.*.media_type' => "median tyyppi",
        'media_images.*.media_path' => "median polku",
        'media_videos.*.media_name' => "median nimi",
        'media_videos.*.media_type' => "median tyyppi",
        'media_videos.*.media_path' => "median polku",
        'documents.*.document_name' => "asiakirjan nimi",
        'documents.*.document_type' => "asiakirjan tyyppi",
        'documents.*.document_path' => "asiakirjan polku",
        'slider_detail.translations.*.lang' => "kielen koodi",
        'skills.*.skill_id' => "taitotunnus",
        'location.city' => "kaupunki",
        'location.country' => "maa",
        'password_confirmation' => "vahvista salasana",
        'translations.*.lang' => "kielen koodi",
        'is_mandatory' => "pakollinen",
		'page_details.translations.*.sections.*.title' => "otsikko",
		'page_details.translations.*.sections.*.description' => "kuvaus",
		'location.city_id' => "kaupunki",
		'location.country_code' => "maan koodi",
		'organisation.organisation_id' => "organisaatiotunnus",
		'mission_detail.*.lang' => "kielen koodi",
        'to_user_id' => "käyttäjätunnus",
        'custom_fields.*.field_id' => "kenttätunnus",
        'settings.*.tenant_setting_id' => "vuokralaisasetuksen tunnus",
        'settings.*.value' => "arvo",
        'option_value.translations.*.lang' => "kielen koodi",
        'timesheet_entries.*.timesheet_id' => "tuntiraporttitunnus",
		'mission_detail.*.short_description' => "lyhyt kuvaus",
        'news_content.translations' => "käännökset",
        'news_content.translations.*.lang' => "kielen koodi",
        'news_content.translations.*.title' => "otsikko",
        'news_content.translations.*.description' => "kuvaus",
        'translations.*.title' => "otsikko",
        'settings.*.notification_type_id' => "ilmoitustyypin tunnus",
        'user_ids.*' => "käyttäjätunnus",
        'mission_detail.*.custom_information' => "omat tiedot",
        'mission_detail.*.custom_information.*.title' => "otsikko",
        'mission_detail.*.custom_information.*.description' => "kuvaus",
        'mission_detail.*.title' => "otsikko",
        'organisation.organisation_name' => "organisaation nimi",
        'cities.*.translations.*.lang' => "kielen koodi",
        'cities.*.translations.*.name' => "nimi",
        'cities.*.translations' => "käännökset",
        'media_images.*.sort_order' => "lajittelujärjestys",
        'media_videos.*.sort_order' => "lajittelujärjestys",
        'documents.*.sort_order' => "lajittelujärjestys",
        'countries.*.translations.*.lang' => "kielen koodi",
        'countries.*.translations.*.name' => "nimi",
        'countries.*.translations' => "käännökset",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "kielen koodi",
        'translations.*.name' => "nimi",
        'translations' => "käännökset",
        'mission_detail.*.section' => "osio",
        'mission_detail.*.section.*.title' => "otsikko",
        'mission_detail.*.section.*.description' => "kuvaus",
		],

];
?>
