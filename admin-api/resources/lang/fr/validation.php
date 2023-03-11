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

    'accepted'             => 'FR Lumen : The :attribute must be accepted.',
    'active_url'           => 'FR Lumen : The :attribute is not a valid URL.',
    'after'                => 'FR Lumen : The :attribute must be a date after :date.',
    'alpha'                => 'FR Lumen : The :attribute may only contain letters.',
    'alpha_dash'           => 'FR Lumen : The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'FR Lumen : The :attribute may only contain letters and numbers.',
    'array'                => 'FR Lumen : The :attribute must be an array.',
    'before'               => 'FR Lumen : The :attribute must be a date before :date.',
    'between'              => [
        'numeric' => 'FR Lumen : The :attribute must be between :min and :max.',
        'file'    => 'FR Lumen : The :attribute must be between :min and :max kilobytes.',
        'string'  => 'FR Lumen : The :attribute must be between :min and :max characters.',
        'array'   => 'FR Lumen : The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'FR Lumen : The :attribute field must be true or false.',
    'confirmed'            => 'FR Lumen : The :attribute confirmation does not match.',
    'date'                 => 'FR Lumen : The :attribute is not a valid date.',
    'date_format'          => 'FR Lumen : The :attribute does not match the format :format.',
    'different'            => 'FR Lumen : The :attribute and :other must be different.',
    'digits'               => 'FR Lumen : The :attribute must be :digits digits.',
    'digits_between'       => 'FR Lumen : The :attribute must be between :min and :max digits.',
    'email'                => 'Fr The :attribute must be a valid email address.',
    'filled'               => 'FR Lumen : The :attribute field is required.',
    'exists'               => 'FR Lumen : The selected :attribute is invalid.',
    'image'                => 'FR Lumen : The :attribute must be an image.',
    'in'                   => 'FR Lumen : The selected :attribute is invalid.',
    'integer'              => 'FR Lumen : The :attribute must be an integer.',
    'ip'                   => 'FR Lumen : The :attribute must be a valid IP address.',
    'max'                  => [
        'numeric' => 'FR Lumen : The :attribute may not be greater than :max.',
        'file'    => 'FR Lumen : The :attribute may not be greater than :max kilobytes.',
        'string'  => 'FR Lumen : The :attribute may not be greater than :max characters.',
        'array'   => 'FR Lumen : The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'FR Lumen : The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'FR Lumen : The :attribute must be at least :min.',
        'file'    => 'FR Lumen : The :attribute must be at least :min kilobytes.',
        'string'  => 'FR Lumen : The :attribute must be at least :min characters.',
        'array'   => 'FR Lumen : The :attribute must have at least :min items.',
    ],
    'not_in'               => 'FR Lumen : The selected :attribute is invalid.',
    'numeric'              => 'FR Lumen : The :attribute must be a number.',
    'regex'                => 'FR Lumen : The :attribute format is invalid.',
    'required'             => 'FR Lumen : The :attribute field is required.',
    'required_if'          => 'FR Lumen : The :attribute field is required when :other is :value.',
    'required_with'        => 'FR Lumen : The :attribute field is required when :values is present.',
    'required_with_all'    => 'FR Lumen : The :attribute field is required when :values is present.',
    'required_without'     => 'FR Lumen : The :attribute field is required when :values is not present.',
    'required_without_all' => 'FR Lumen : The :attribute field is required when none of :values are present.',
    'same'                 => 'FR Lumen : The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'FR Lumen : The :attribute must be :size.',
        'file'    => 'FR Lumen : The :attribute must be :size kilobytes.',
        'string'  => 'FR Lumen : The :attribute must be :size characters.',
        'array'   => 'FR Lumen : The :attribute must contain :size items.',
    ],
    'timezone'             => 'FR Lumen : The :attribute must be a valid zone.',
    'unique'               => 'FR Lumen : The :attribute has already been taken.',
    'url'                  => 'FR Lumen : The :attribute format is invalid.',

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
        'attribute-name' => [
            'rule-name' => 'custom-message',
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

    'attributes' => [],

];