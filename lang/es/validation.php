<?php

/*
 * Spanish validation messages.
 *
 * APP_LOCALE and APP_FALLBACK_LOCALE were both `es` while the framework ships
 * only `en`, so every failed rule in the app rendered as its raw key: a form
 * that rejected input showed the user "validation.required". The fallback is
 * now `en`, so a rule this file does not translate degrades to an English
 * sentence rather than to a key.
 *
 * Only the rules the app's form requests actually use are translated. Adding a
 * rule to a request means adding it here; the English fallback is the safety
 * net, not the plan.
 *
 * The wording avoids adjectives that agree with :attribute, because the
 * attributes below are both genders: ":Attribute seleccionado no existe" reads
 * as "La región seleccionado no existe". Phrasings that agree with a noun the
 * message itself supplies ("no es una opción válida") work for every attribute.
 */
return [
    'accepted' => 'Debe aceptar :attribute.',
    'after' => ':Attribute debe ser una fecha posterior a :date.',
    'array' => ':Attribute debe ser una lista.',
    'before' => ':Attribute debe ser una fecha anterior a :date.',
    'boolean' => ':Attribute debe ser sí o no.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => ':Attribute no es una fecha válida.',
    'date_format' => ':Attribute no corresponde al formato :format.',
    'different' => ':Attribute y :other deben ser distintos.',
    'digits' => ':Attribute debe tener :digits dígitos.',
    'digits_between' => ':Attribute debe tener entre :min y :max dígitos.',
    'email' => ':Attribute debe ser un correo válido.',
    'exists' => ':Attribute no existe.',
    'in' => ':Attribute no es una opción válida.',
    'integer' => ':Attribute debe ser un número entero.',
    'numeric' => ':Attribute debe ser un número.',
    'required' => 'Debe completar :attribute.',
    'required_if' => 'Debe completar :attribute cuando :other es :value.',
    'same' => ':Attribute y :other deben coincidir.',
    'string' => ':Attribute debe ser texto.',
    'unique' => ':Attribute ya existe.',

    'max' => [
        'array' => ':Attribute no puede tener más de :max elementos.',
        'file' => ':Attribute no puede pesar más de :max kilobytes.',
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'array' => ':Attribute debe tener al menos :min elementos.',
        'file' => ':Attribute debe pesar al menos :min kilobytes.',
        'numeric' => ':Attribute debe ser al menos :min.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
    ],

    /*
     * Field names as they read on screen, so a message names the label the user
     * saw rather than the column. Without these, ":attribute" falls back to the
     * snake_case column and produces "Debe completar id cliente".
     */
    'attributes' => [
        'activa' => 'la tienda activa',
        'back' => 'el back',
        'cargo_fijo' => 'el cargo fijo',
        'casofwa' => 'el caso FWA',
        'casosf' => 'el caso SF',
        'ciclo' => 'el ciclo',
        'clave' => 'la contraseña',
        'comentario' => 'el comentario',
        'comentario_back' => 'el comentario del back',
        'comentario_ejecutivo' => 'el comentario',
        'comentario_liquidador' => 'el comentario del liquidador',
        'comentario_validador' => 'el comentario del validador',
        'comision' => 'la comisión',
        'contacto' => 'el nombre del contacto',
        'correo' => 'el correo',
        'cuenta_financiera' => 'la cuenta financiera',
        'descuento' => 'el descuento',
        'detalle_equipo' => 'el detalle del equipo',
        'estado' => 'el estado',
        'etapa' => 'la fase',
        'fecha_activacion' => 'la fecha de activación',
        'fecha_actualizacion' => 'la fecha del estado',
        'fecha_estimada' => 'la fecha estimada de cierre',
        'fecha_inicio_averia' => 'la fecha de inicio de la avería',
        'fecha_liquidacion' => 'la fecha de liquidación',
        'fecha_nacimiento' => 'la fecha de nacimiento',
        'fecha_ticket_atencion' => 'la fecha del ticket',
        'id_cartera' => 'el ejecutivo TDP',
        'id_cliente' => 'el cliente',
        'id_respuesta' => 'la respuesta',
        'id_rol' => 'el rol',
        'menus' => 'los accesos',
        'modalidad' => 'la modalidad',
        'nivel_negociacion' => 'la probabilidad de cierre',
        'nodo' => 'el nodo',
        'nombre_contacto' => 'el nombre del contacto',
        'nombre_rol' => 'el nombre del rol',
        'numero_problema' => 'la cantidad de líneas con avería',
        'numero_ticket' => 'el número de ticket',
        'observacion' => 'la observación',
        'oportunidad' => 'la oportunidad',
        'origen' => 'el origen',
        'personal' => 'el nombre del personal',
        'problema_equipo' => 'el problema con el equipo',
        'q_lineas' => 'la cantidad de líneas',
        'razon_social' => 'la razón social',
        'ruc' => 'el RUC',
        'servicio' => 'el servicio',
        'sexo' => 'el sexo',
        'telefono' => 'el teléfono',
        'telefono1' => 'el teléfono 1',
        'telefono2' => 'el teléfono 2',
        'tiendas' => 'las tiendas',
        'tipo_averia' => 'el tipo de avería',
        'tipo_data' => 'el segmento',
        'tipo_producto' => 'el tipo de producto',
        'usuario' => 'el usuario',
        'validacion' => 'la decisión',
        'zonal' => 'la región',
        'zonal_telefonica' => 'la región',
    ],
];
