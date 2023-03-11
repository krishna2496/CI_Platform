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

    'accepted'             => "必须接受 :attribute。",
    'active_url'           => ":attribute 不是有效的 URL。",
    'after'                => ":attribute 必须是 :date 之后的日期。",
    'alpha'                => ":attribute 只能包含字母。",
    'alpha_dash'           => ":attribute 只能包含字母、数字和连接号。",
    'alpha_num'            => ":attribute 只能包含字母和数字。",
    'array'                => ":attribute 必须是数组。",
    'before'               => ":attribute 必须是 :date 之前的日期。",
    'between'              => [
        'numeric' => ":attribute 必须位于 :min 和 :max 之间。",
        'file'    => ":attribute 必须位于 :min 和 :max 千字节之间。",
        'string'  => ":attribute 必须位于 :min 和 :max 个字符之间。",
        'array'   => ":attribute 必须位于 :min 和 :max 项之间。",
    ],
    'boolean'              => ":attribute 字段必须为真或假。",
    'confirmed'            => ":attribute 确认不相符。",
    'date'                 => ":attribute 不是有效的日期。",
    'date_format'          => ":attribute 与格式 :format 不匹配。",
    'different'            => ":attribute 与 :other 必须不同。",
    'digits'               => ":attribute 必须是 :digits 数位。",
    'digits_between'       => ":attribute 必须位于 :min 和 :max 数位之间。",
    'email'                => ":attribute 必须是有效的电子邮件地址。",
    'filled'               => ":attribute 字段是必填字段。",
    'exists'               => "已选定的 :attribute 无效。",
    'image'                => ":attribute 必须是图像。",
    'in'                   => "已选定的 :attribute 无效。",
    'integer'              => ":attribute 必须是整数。",
    'ip'                   => ":attribute 必须是有效的 IP 地址。",
    'max'                  => [
        'numeric' => ":attribute 不得超过 :max。",
        'file'    => ":attribute 不得超过 :max 千字节。",
        'string'  => ":attribute 不得超过 :max 个字符。",
        'array'   => ":attribute 不得超过 :max 项。",
    ],
    'mimes'                => ":attribute 必须是以下文件类型：。:values。",
    'mimetypes'            => ":attribute 必须是以下文件类型：。:values。",
    'min'                  => [
        'numeric' => ":attribute 必须至少是 :min。",
        'file'    => ":attribute 必须至少是 :min 千字节。",
        'string'  => ":attribute 必须至少是 :min 个字符。",
        'array'   => ":attribute 必须至少是 :min 项。",
    ],
    'not_in'               => "已选定的 :attribute 无效。",
    'numeric'              => ":attribute 必须是数字。",
    'regex'                => ":attribute 格式无效。",
    'required'             => ":attribute 字段是必填字段。",
    'required_if'          => ":other 为 :value 时，:attribute 字段为必填字段。",
    'required_with'        => ":value 存在时，:attribute 字段为必填字段。",
    'required_with_all'    => ":value 存在时，:attribute 字段为必填字段。",
    'required_without'     => ":value 不存在时，:attribute 字段为必填字段。",
    'required_without_all' => "没有任何 :value 存在时，:attribute 字段为必填字段。",
    'same'                 => ":attribute 与 :other 必须匹配。",
    'size'                 => [
        'numeric' => ":attribute 必须是 :size。",
        'file'    => ":attribute 必须是 :size 千字节。",
        'string'  => ":attribute 必须是 :size 个字符。",
        'array'   => ":attribute 必须是 :size 项。",
    ],
    'timezone'             => ":attribute 必须是有效的区。",
    'unique'               => ":attribute 已被取用。",
    'url'                  => ":attribute 格式无效。",
    'present'              => ":attribute 字段是必填字段",
	'distinct'             => ":attribute 字段为重复值。",

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
            'valid_media_path' => "请输入有效的媒体图像",
        ],
        'documents.*.document_path' => [
            'valid_document_path' => "请输入有效的文档文件",
        ],
		'media_videos.*.media_path' => [
            'valid_video_url' => "请输入有效的 youtube url",
        ],
		'avatar' => [
            'valid_profile_image' => "不允许的无效图像文件或图像类型。允许的类型：png、jpeg、jpg",
        ],
		'parent_skill' => [
            'valid_parent_skill' => "无效的父类技能",
        ],
        'url' => [
            'valid_media_path' => "请输入有效的图像 url",
        ],
        'linked_in_url' => [
            'valid_linkedin_url' => "请输入有效的 linkedIn url",
        ],
        'documents.*' => [
            'valid_timesheet_document_type' => "请选择有效的时间表文件",
            'max' =>
                "文档文件大小必须为 "
                . (config('constants.TIMESHEET_DOCUMENT_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'date_volunteered' => [
            'before' => "您无法为未来的日期添加时间条目",
        ],
        'news_image' => [
            'valid_media_path' => "请输入有效的媒体图像",
        ],
        'user_thumbnail' => [
            'valid_media_path' => "请输入有效的媒体图像",
        ],
        'story_images.*' => [
            'valid_story_image_type' => "请选择有效的图像类型",
            'max' =>
                "图像大小必须为 "
                . (config('constants.STORY_IMAGE_SIZE_LIMIT') / 1024)
                . 'mb or below',
        ],
        'story_videos' => [
            'valid_story_video_url' => "请输入有效的视频 url",
            'max_video_url' => "最大 ".config('constants.STORY_MAX_VIDEO_LIMIT').' video url can be added',
        ],
        'story_images' => [
            'max' => "最大 ".config('constants.STORY_MAX_IMAGE_LIMIT').' images can be added',
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
        'page_details.translations' => "翻译",
        'page_details.translations.*.lang' => "语言代码",
        'page_details.translations.*.title' => "标题",
        'page_details.translations.*.sections' => "段",
        'translations.*.values' => "值",
        'media_images.*.media_name' => "媒体名称",
        'media_images.*.media_type' => "媒体类型",
        'media_images.*.media_path' => "媒体路径",
        'media_videos.*.media_name' => "媒体名称",
        'media_videos.*.media_type' => "媒体类型",
        'media_videos.*.media_path' => "媒体路径",
        'documents.*.document_name' => "文件名称",
        'documents.*.document_type' => "文件类型",
        'documents.*.document_path' => "文件路径",
        'slider_detail.translations.*.lang' => "语言代码",
        'skills.*.skill_id' => "技能 id",
        'location.city' => "城市",
        'location.country' => "国家/地区",
        'password_confirmation' => "确认密码",
        'translations.*.lang' => "语言代码",
        'is_mandatory' => "必填",
		'page_details.translations.*.sections.*.title' => "标题",
		'page_details.translations.*.sections.*.description' => "说明",
		'location.city_id' => "城市",
		'location.country_code' => "国家代码",
		'organisation.organisation_id' => "组织 id",
		'mission_detail.*.lang' => "语言代码",
        'to_user_id' => "用户 id",
        'custom_fields.*.field_id' => "字段 id",
        'settings.*.tenant_setting_id' => "租户设置 id",
        'settings.*.value' => "值",
        'option_value.translations.*.lang' => "语言代码",
        'timesheet_entries.*.timesheet_id' => "时间表 id",
		'mission_detail.*.short_description' => "简短说明",
        'news_content.translations' => "翻译",
        'news_content.translations.*.lang' => "语言代码",
        'news_content.translations.*.title' => "标题",
        'news_content.translations.*.description' => "说明",
        'translations.*.title' => "标题",
        'settings.*.notification_type_id' => "通知类型 id",
        'user_ids.*' => "用户 id",
        'mission_detail.*.custom_information' => "自定义信息",
        'mission_detail.*.custom_information.*.title' => "标题",
        'mission_detail.*.custom_information.*.description' => "说明",
        'mission_detail.*.title' => "标题",
        'organisation.organisation_name' => "组织名称",
        'cities.*.translations.*.lang' => "语言代码",
        'cities.*.translations.*.name' => "名称",
        'cities.*.translations' => "翻译",
        'media_images.*.sort_order' => "排序",
        'media_videos.*.sort_order' => "排序",
        'documents.*.sort_order' => "排序",
        'countries.*.translations.*.lang' => "语言代码",
        'countries.*.translations.*.name' => "名称",
        'countries.*.translations' => "翻译",
        'countries.*.iso' => "ISO",
        'translations.*.lang' => "语言代码",
        'translations.*.name' => "名称",
        'translations' => "翻译",
        'mission_detail.*.section' => "段",
        'mission_detail.*.section.*.title' => "标题",
        'mission_detail.*.section.*.description' => "说明",
		],

];
?>
