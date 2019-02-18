<?php

namespace ElectronicInvoicing;

use DateTime;
use ElectronicInvoicing\VoucherType;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'emission_point_id',
        'voucher_type_id',
        'environment_id',
        'voucher_state_id',
        'sequential',
        'numeric_code',
        'customer_id',
        'issue_date',
        'authorization_date',
        'currency_id',
        'tip',
        'iva_retention',
        'rent_retention',
        'xml',
        'extra_detail',
        'user_id',
        'support_document',
        'support_document_date'
    ];

    public function emissionPoint()
    {
        return $this->belongsTo('ElectronicInvoicing\EmissionPoint');
    }

    public function user()
    {
        return $this->belongsTo('ElectronicInvoicing\User');
    }

    public function customer()
    {
        return $this->belongsTo('ElectronicInvoicing\Customer');
    }

    public function currency()
    {
        return $this->belongsTo('ElectronicInvoicing\Currency');
    }

    public function type()
    {
        return $this->belongsTo('ElectronicInvoicing\VoucherType');
    }

    public function state()
    {
        return $this->belongsTo('ElectronicInvoicing\VoucherState');
    }

    public function environment()
    {
        return $this->belongsTo('ElectronicInvoicing\Environment');
    }

    public function details()
    {
        return $this->hasMany('ElectronicInvoicing\Detail');
    }

    public function additionalFields()
    {
        return $this->hasMany('ElectronicInvoicing\AdditionalField');
    }

    public function payments()
    {
        return $this->hasMany('ElectronicInvoicing\Payment');
    }

    public function creditNotes()
    {
        return $this->hasMany('ElectronicInvoicing\CreditNote');
    }

    public function debitNotesTaxes()
    {
        return $this->hasMany('ElectronicInvoicing\DebitNoteTax');
    }

    public function retentions()
    {
        return $this->hasMany('ElectronicInvoicing\Retention');
    }

    public function waybills()
    {
        return $this->hasMany('ElectronicInvoicing\Waybill');
    }

    public function subtotalWithoutTaxes()
    {
        $subtotalWithoutTaxes = 0.0;
        switch ($this->voucher_type_id) {
            case 1:
                foreach ($this->details()->get() as $detail) {
                    $subtotalWithoutTaxes += $detail->quantity * $detail->unit_price - $detail->discount;
                }
            case 3:
                $subtotalWithoutTaxes += $this->debitNotesTaxes()->first()->tax_base;
                break;
        }
        return $subtotalWithoutTaxes;
    }

    public function totalDiscounts()
    {
        if (count($this->details()->get()) === 0) {
            return NULL;
        }
        $discounts = 0.0;
        foreach ($this->details()->get() as $detail) {
            $discounts += $detail->discount;
        }
        return $discounts;
    }

    public function total()
    {
        $total = self::subtotalWithoutTaxes();
        switch ($this->voucher_type_id) {
            case 1:
                foreach ($this->details()->get() as $detail) {
                    $total += $detail->taxDetails()->first()->value;
                }
            case 3:
                $total *= (1 + $this->debitNotesTaxes()->first()->rate / 100.0);
                break;
        }
        return $total;
    }

    public function ivaBreakdown()
    {
        $iva = array();
        foreach (IvaTax::all() as $ivaTax) {
            $iva[strval($ivaTax->auxiliary_code)] = 0.00;
        }
        switch ($this->voucher_type_id) {
            case 1:
                foreach ($this->details()->get() as $detail) {
                    foreach ($detail->taxDetails()->get() as $tax) {
                        if ($tax->code === 2) {
                            $iva[strval($tax->percentage_code)] += $detail->quantity * $detail->unit_price - $detail->discount;
                        }
                    }
                }
                break;
            case 3:
                $iva[strval($this->debitNotesTaxes()->first()->percentage_code)] += $this->debitNotesTaxes()->first()->tax_base;
                break;
        }
        return $iva;
    }

    public function iva()
    {
        $iva = 0.00;
        switch ($this->voucher_type_id) {
            case 1:
                foreach ($this->details()->get() as $detail) {
                    foreach ($detail->taxDetails()->get() as $tax) {
                        if ($tax->code === 2 && ($tax->percentage_code === 2 || $tax->percentage_code === 3)) {
                            $iva += $tax->value;
                        }
                    }
                }
                break;
            case 3:
                $iva += $this->debitNotesTaxes()->first()->tax_base * $this->debitNotesTaxes()->first()->rate / 100.0;
                break;
        }
        return $iva;
    }

    public function accessKey()
    {
        $accessKey = DateTime::createFromFormat('Y-m-d', $this->issue_date)->format('dmY') .
            str_pad(strval(VoucherType::find($this->voucher_type_id)->code), 2, '0', STR_PAD_LEFT) .
            $this->emissionPoint->branch->company->ruc .
            $this->environment->code .
            str_pad(strval($this->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) .
            str_pad(strval($this->emissionPoint->code), 3, '0', STR_PAD_LEFT) .
            str_pad(strval($this->sequential), 9, '0', STR_PAD_LEFT) .
            str_pad(strval($this->numeric_code), 8, '0', STR_PAD_LEFT) .
            '1';
        return $accessKey . self::getCheckDigit($accessKey);
    }

    private static function getCheckDigit($accessKey)
    {
        $summation = 0;
        $factor = 7;
        foreach (str_split($accessKey) as $number) {
            $summation += intval($number) * $factor--;
            $factor = $factor == 1 ? 7 : $factor;
        }
        $checkDigit = 11 - $summation % 11;
        return $checkDigit == 10 ? 1 : ($checkDigit == 11 ? 0 : $checkDigit);
    }
}
