<?php

namespace ElectronicInvoicing\StaticClasses;

class RetentionType
{
    private const RETENTION_TYPES = [
        [
            'id' => 1,
            'name' => 'COMPROBANTE DE RETENCIÓN'
        ], [
            'id' => 2,
            'name' => 'COMPROBANTE DE RETENCIÓN ATS'
        ]
    ];

    public static function getRetentionTypes()
    {
        $retentionTypes = array();
        foreach (RetentionType::RETENTION_TYPES as $retentionType) {
            array_push($retentionTypes, (new ObjectCast)->getObject($retentionType['id'], $retentionType['name']));
        }
        return collect($retentionTypes);
    }
}
