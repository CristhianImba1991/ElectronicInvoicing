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
        if ($this->method() === 'POST') {
            return [
                'main_code' => 'required|max:3',
                'establishment' => 'required|min:1|max:999|integer',
                'name' => 'required|max:300',
                'address' => 'required|max:300',
                'phone' => 'required|max:30',
            ];
        } else {
            return [
                'company' => 'required|exists:companies,id',
                'establishment' => 'required|min:1|max:999|integer',
                'name' => 'required|max:300',
                'address' => 'required|max:300',
                'phone' => 'required|max:30',
                'main_code' => 'required|max:3'
            ];
        }
    }
}
