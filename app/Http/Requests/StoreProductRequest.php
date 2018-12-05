<?php

namespace ElectronicInvoicing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->method() === 'PUT') {
            return [
                'main_code' => 'required|max:25',
                'auxiliary_code' => 'required|max:25',
                //'company' => 'required|exists:companies,id',
                //'branch' => 'required|exists:branches,id',
                'unit_price' => 'required|gt:0',
                'stock' => 'required|gt:0',
                'description'=> 'required|max:300',
                //'iva_tax'=> 'required'
            ];
        } else {
            return [
                'main_code' => 'required|max:25',
                'auxiliary_code' => 'required|max:25',
                'company' => 'required|exists:companies,id',
                'branch' => 'required|exists:branches,id',
                'unit_price' => 'required|gt:0',
                'stock' => 'required|gt:0',
                'description'=> 'required|max:300',
            ];
        }
    }
}
