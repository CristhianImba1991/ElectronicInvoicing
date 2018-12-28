<?php

namespace ElectronicInvoicing\Http\Logic;

use Carbon\Carbon;
use ElectronicInvoicing\User;
use Illuminate\Http\Request;
use Storage;

class DraftJson
{
    private $dataArray;
    private static $INSTANCE = NULL;

    private function __construct() {
        $this->dataArray = json_decode(Storage::exists('drafts.json') ? Storage::get('drafts.json') : "[]", true);
    }

    private function saveToFile()
    {
        Storage::put('drafts.json', json_encode($this->dataArray, JSON_PRETTY_PRINT));
    }

    public static function getInstance()
    {
        if (self::$INSTANCE === NULL) {
            self::$INSTANCE = new DraftJson();
        }
        return self::$INSTANCE;
    }

    public function appendUser(User $user)
    {
        if (!isset($this->dataArray[strval($user->id)])) {
            $this->dataArray[strval($user->id)] = array();
            ksort($this->dataArray);
            $this->saveToFile();
        }
        return TRUE;
    }

    public function removeUser(User $user)
    {
        if (isset($this->dataArray[strval($user->id)])) {
            unset($this->dataArray[strval($user->id)]);
            $this->saveToFile();
        }
        return TRUE;
    }

    public function storeDraftVoucher(User $user, Request $request)
    {
        if (isset($this->dataArray[strval($user->id)])) {
            array_push($this->dataArray[strval($user->id)], $this->formatRequest(count($this->dataArray[strval($user->id)]) + 1, $request, Carbon::now(), Carbon::now()));
            $this->saveToFile();
        }
        return TRUE;
    }

    public function updateDraftVoucher(User $user, $id, Request $request)
    {
        if (isset($this->dataArray[strval($user->id)])) {
            if ($id > 0 && count($this->dataArray[strval($user->id)]) >= $id) {
                $this->dataArray[strval($user->id)][$id - 1] = $this->formatRequest($id, $request, $this->dataArray[strval($user->id)][$id - 1]['created_at'], Carbon::now());
                $this->saveToFile();
                return TRUE;
            }
        }
        return FALSE;
    }

    public function deleteDraftVoucher(User $user, $id)
    {
        if (isset($this->dataArray[strval($user->id)])) {
            for ($i = 0; $i < count($this->dataArray[strval($user->id)]); $i++) {
                if ($this->dataArray[strval($user->id)][$i]['id'] === $id) {
                    array_splice($this->dataArray[strval($user->id)], $i, 1);
                    break;
                }
            }
            for ($i = 0; $i < count($this->dataArray[strval($user->id)]); $i++) {
                $this->dataArray[strval($user->id)][$i]['id'] = $i + 1;
            }
            $this->saveToFile();
            return TRUE;
        }
        return FALSE;
    }

    public function getDraftVouchers(User $user)
    {
        if (isset($this->dataArray[strval($user->id)])) {
            return $this->dataArray[strval($user->id)];
        }
        return FALSE;
    }

    public function getDraftVoucher(User $user, $id)
    {
        if (isset($this->dataArray[strval($user->id)])) {
            if ($id > 0 && count($this->dataArray[strval($user->id)]) >= $id) {
                return $this->dataArray[strval($user->id)][$id - 1];
            }
        }
        return FALSE;
    }

    private function formatRequest($id, Request $request, $createdAt, $updatedAt)
    {
        $voucher = array('id' => $id) + $request->except([
            '_token',
            'company_ruc',
            'company_name',
            'company_address',
            'branch_address',
            'company_special_contributor',
            'company_keep_accounting',
            'customer_identification',
            'customer_address',
            'product-description',
            'product-iva',
            'product-subtotal'
        ]);
        function isNotNull($var)
        {
            return !is_null($var);
        }
        if (array_key_exists('product', $voucher)) {
            $voucher['product'] = array_values(array_filter($voucher['product'], function ($var) {
                return !is_null($var);
            }));
        }
        if (array_key_exists('product_quantity', $voucher)) {
            $voucher['product_quantity'] = array_values(array_filter($voucher['product_quantity'], function ($var) {
                return !is_null($var);
            }));
        }
        if (array_key_exists('product_unitprice', $voucher)) {
            $voucher['product_unitprice'] = array_values(array_filter($voucher['product_unitprice'], function ($var) {
                return !is_null($var);
            }));
        }
        if (array_key_exists('product_discount', $voucher)) {
            $voucher['product_discount'] = array_values(array_filter($voucher['product_discount'], function ($var) {
                return !is_null($var);
            }));
        }
        $voucher['created_at'] = $createdAt;
        $voucher['updated_at'] = $updatedAt;
        return $voucher;
    }
}
