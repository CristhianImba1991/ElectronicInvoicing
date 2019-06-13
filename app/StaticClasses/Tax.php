<?php

namespace ElectronicInvoicing\StaticClasses;

class Tax
{
    private const TAXES = [
        [
            'id' => 2,
            'name' => 'IVA'
        ], [
            'id' => 3,
            'name' => 'ICE'
        ], [
            'id' => 5,
            'name' => 'IRBPNR'
        ]
    ];

    public static function getTaxTypes()
    {
        $taxTypes = array();
        foreach (Tax::TAXES as $tax) {
            array_push($taxTypes, (new ObjectCast)->getObject($tax['id'], $tax['name']));
        }
        return collect($taxTypes);
    }
}
