<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Maximum number of attempts
    |--------------------------------------------------------------------------
    |
    | This value is the maximum number of attempts that the voucher can be
    | authorized by day.
    |
    */

    'max_attempts' => 3,

    /*
    |--------------------------------------------------------------------------
    | SRI Web Services
    |--------------------------------------------------------------------------
    |
    | This value is the URLs of the web services published by SRI to validate
    | and authorize the vouchers (1 for TEST and 2 for PRODUCTION).
    |
    */

    'ws_validate' => [
        1 => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
        2 => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
    ],

    'ws_authorize' => [
        1 => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        2 => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
    ],
    
];
