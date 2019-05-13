<?php

namespace ElectronicInvoicing\StaticClasses;

class VoucherAbbreviations
{
    public static function getAbbreviation($voucherType)
    {
        switch ($voucherType) {
            case 1: return 'FC'; break;
            case 2: return 'NC'; break;
            case 3: return 'ND'; break;
            case 4: return 'GR'; break;
            case 5: return 'CR'; break;
        }
    }
}
