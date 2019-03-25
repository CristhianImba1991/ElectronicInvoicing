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

    'accepted'             => 'El campo :ATTRIBUTE debe ser aceptado.',
    'active_url'           => 'El campo :ATTRIBUTE no es una URL válida.',
    'after'                => 'El campo :ATTRIBUTE debe ser una fecha posterior a :date.',
    'after_or_equal'       => 'El campo :ATTRIBUTE debe ser una fecha posterior o igual a :date.',
    'alpha'                => 'El campo :ATTRIBUTE sólo puede contener letras.',
    'alpha_dash'           => 'El campo :ATTRIBUTE solo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num'            => 'El campo :ATTRIBUTE sólo puede contener letras y números.',
    'array'                => 'El campo :ATTRIBUTE debe ser un arreglo.',
    'before'               => 'El campo :ATTRIBUTE debe ser una fecha anterior a :date.',
    'before_or_equal'      => 'El campo :ATTRIBUTE debe ser una fecha anterior o igual a :date.',
    'between'              => [
        'numeric' => 'El campo :ATTRIBUTE debe estar entre :min y :max.',
        'file'    => 'El campo :ATTRIBUTE debe estar entre :min y :max kilobytes.',
        'string'  => 'El campo :ATTRIBUTE debe estar entre :min y :max caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe tener entre :min y :max elementos.',
    ],
    'boolean'              => 'El campo :ATTRIBUTE debe ser verdadero o falso.',
    'confirmed'            => 'La confirmación del campo :ATTRIBUTE no coincide.',
    'date'                 => 'El campo :ATTRIBUTE no es una fecha válida.',
    'date_format'          => 'El campo :ATTRIBUTE no coincide con el formato :format.',
    'different'            => 'El campo :ATTRIBUTE y :OTHER deben ser diferentes.',
    'digits'               => 'El campo :ATTRIBUTE debe contener :digits dígitos.',
    'digits_between'       => 'El campo :ATTRIBUTE debe estar entre :min y :max dígitos.',
    'dimensions'           => 'El campo :ATTRIBUTE tiene dimensiones de imagen no válidas.',
    'distinct'             => 'El campo :ATTRIBUTE tiene un valor duplicado.',
    'email'                => 'El campo :ATTRIBUTE debe ser una dirección válida de correo electrónico.',
    'exists'               => 'El valor seleccionado del campo :ATTRIBUTE no es válido.',
    'file'                 => 'El campo :ATTRIBUTE debe ser un archivo.',
    'filled'               => 'El campo :ATTRIBUTE debe contener un valor.',
    'gt'                   => [
        'numeric' => 'El campo :ATTRIBUTE debe ser mayor a :value.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE debe ser mayor a :value kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE debe ser mayor a :value caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe tener más de :value elementos.',
    ],
    'gte'                  => [
        'numeric' => 'El campo :ATTRIBUTE debe ser mayor o igual a :value.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE debe ser mayor o igual a :value kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE debe ser mayor o igual a :value caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe tener :value elementos o más.',
    ],
    'image'                => 'El campo :ATTRIBUTE debe ser una imagen.',
    'in'                   => 'El valor seleccionado del campo :ATTRIBUTE no es válido.',
    'in_array'             => 'El campo :ATTRIBUTE no existe en :OTHER.',
    'integer'              => 'El campo :ATTRIBUTE debe ser un número entero.',
    'ip'                   => 'El campo :ATTRIBUTE debe ser una dirección IP válida.',
    'ipv4'                 => 'El campo :ATTRIBUTE debe ser una dirección IPv4 válida.',
    'ipv6'                 => 'El campo :ATTRIBUTE debe ser una dirección IPv6 válida.',
    'json'                 => 'El campo :ATTRIBUTE debe ser un texto JSON válido.',
    'lt'                   => [
        'numeric' => 'El campo :ATTRIBUTE debe ser menor a :value.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE debe ser menor a :value kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE debe ser menor a :value caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe contener menos de :value elementos.',
    ],
    'lte'                  => [
        'numeric' => 'El campo :ATTRIBUTE debe ser menor o igual a :value.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE debe ser menor o igual a :value kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE debe ser menor o igual a :value caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe contener :value elementos o menos.',
    ],
    'max'                  => [
        'numeric' => 'El campo :ATTRIBUTE no debe ser mayor a :max.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE no debe ser mayor a :max kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE no debe ser mayor a :max caracteres.',
        'array'   => 'El campo :ATTRIBUTE no debe contener más de :max elementos.',
    ],
    'mimes'                => 'El campo :ATTRIBUTE debe ser un archivo del tipo: :values.',
    'mimetypes'            => 'El campo :ATTRIBUTE debe ser un archivo del tipo: :values.',
    'min'                  => [
        'numeric' => 'El campo :ATTRIBUTE debe ser de al menos :min.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE debe ser de al menos :min kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE debe ser de al menos :min caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe contener al menos :min elementos.',
    ],
    'not_in'               => 'El valor seleccionado del campo :ATTRIBUTE no es válido.',
    'not_regex'            => 'El formato del campo :ATTRIBUTE no es válido.',
    'numeric'              => 'El campo :ATTRIBUTE debe ser un número.',
    'present'              => 'El campo :ATTRIBUTE debe estar presente.',
    'regex'                => 'El formato del campo :ATTRIBUTE no es válido.',
    'required'             => 'El campo :ATTRIBUTE es requerido.',
    'required_if'          => 'El campo :ATTRIBUTE es requerido cuando el campo :OTHER es :value.',
    'required_unless'      => 'El campo :ATTRIBUTE es requerido a menos que el campo :OTHER tenga uno de los siguientes valores: :values.',
    'required_with'        => 'El campo :ATTRIBUTE es requerido cuando el/los campo/s :values está/n presente/s.',
    'required_with_all'    => 'El campo :ATTRIBUTE es requerido cuando el/los campo/s :values está/n presente/s.',
    'required_without'     => 'El campo :ATTRIBUTE es requerido cuando el/los campo/s :values no está/n presente/s.',
    'required_without_all' => 'El campo :ATTRIBUTE es requerido cuando ninguno de los campos :values están presentes.',
    'same'                 => 'El campo :ATTRIBUTE y :OTHER deben coincidir.',
    'size'                 => [
        'numeric' => 'El campo :ATTRIBUTE debe ser :size.',
        'file'    => 'El peso del archivo del campo :ATTRIBUTE debe ser de :size kilobytes.',
        'string'  => 'La longitud del campo :ATTRIBUTE debe ser de :size caracteres.',
        'array'   => 'El campo :ATTRIBUTE debe contener :size elementos.',
    ],
    'string'               => 'El campo :ATTRIBUTE debe ser texto.',
    'timezone'             => 'El campo :ATTRIBUTE debe ser una zona horaria válida.',
    'unique'               => 'El campo :ATTRIBUTE ya ha sido tomado.',
    'uploaded'             => 'El campo :ATTRIBUTE falló en cargarse.',
    'url'                  => 'El formato del campo :ATTRIBUTE no es válido.',

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
        'ruc' => [
            'validruc' => 'El campo :ATTRIBUTE no es válido.',
        ],
        'sign' => [
            'validsign' => 'No se puede leer el almacén de certificados o la contraseña es incorrecta.',
        ],
        'establishment' => [
            'uniquemultiple' => 'El campo :ATTRIBUTE ya ha sido tomado.',
        ],
        'code' => [
            'uniquemultiple' => 'El campo :ATTRIBUTE ya ha sido tomado.',
        ],
        'main_code' => [
            'uniquemultiple' => 'El campo :ATTRIBUTE ya ha sido tomado.',
        ],
        'company' => [
            'required_unless' => 'El campo :ATTRIBUTE es requerido.',
        ],
        'branch' => [
            'required_unless' => 'El campo :ATTRIBUTE es requerido.',
        ],
        'emission_point' => [
            'required_unless' => 'El campo :ATTRIBUTE es requerido.',
        ],
        'identification' => [
            'uniquecustomer' => 'El campo :ATTRIBUTE ya ha sido tomado.',
        ],
        'email' => [
            'validemailmultiple' => 'El campo :ATTRIBUTE debe ser una dirección válida de correo electrónico.',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'ruc' => 'RUC',
        'social_reason' => 'Razón social',
        'tradename' => 'Nombre comercial',
        'address' => 'Dirección',
        'special_contributor' => 'Contribuidor especial',
        'phone' => 'Teléfono',
        'logo' => 'Logotipo',
        'sign' => 'Firma electrónica',
        'password' => 'Contraseña',
        'company' => 'Compañía',
        'establishment' => 'Establecimiento',
        'name' => 'Nombre',
        'branch' => 'Sucursal',
        'code' => 'Código',
        'role' => 'Rol',
        'email' => 'Correo electrónico',
        'emission_point' => 'Punto de emisión',
        'identification_type' => 'Tipo de identificación',
        'identification' => 'Identificación',
        'main_code' => 'Código principal',
        'auxiliary_code' => 'Código auxiliar',
        'unit_price' => 'Precio unitario',
        'stock' => 'Existencias',
        'description'=> 'Descripción',
        'customer'=> 'Cliente',
        'currency' => 'Moneda',
        'issue_date' => 'Fecha emisión',
        'environment' => 'Ambiente',
        'voucher_type' => 'Tipo de comprobante',

        'product' => 'Producto',
        'product_quantity' => 'Cantidad',
        'product_unitprice' => 'Precio unitario',
        'product_discount' => 'Descuento',
        'paymentMethod' => 'Método de pago',
        'paymentMethod_value' => 'Valor del método de pago',
        'paymentMethod_timeunit' => 'Unidad de tiempo',
        'paymentMethod_term' => 'Plazo',

        /*$rules['additionaldetail_name'] = 'array|max:3';
        $rules['additionaldetail_value'] = 'array|max:3';
        $rules['waybill_establishment'] = 'required_with:waybill_emissionpoint,waybill_sequential|nullable|integer|min:1|max:999';
        $rules['waybill_emissionpoint'] = 'required_with:waybill_establishment,waybill_sequential|nullable|integer|min:1|max:999';
        $rules['waybill_sequential'] = 'required_with:waybill_establishment,waybill_emissionpoint|nullable|integer|min:1|max:999999999';
        $rules['extra_detail'] = 'nullable|string';
        $rules['ivaRetentionValue'] = 'nullable|numeric|min:0';
        $rules['rentRetentionValue'] = 'nullable|numeric|min:0';
        $rules['tip'] = 'required|numeric|min:0';*/
    ],

];
