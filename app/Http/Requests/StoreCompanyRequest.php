<?php

namespace ElectronicInvoicing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
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
                'ruc' => 'required|digits:13|validruc',
                'social_reason' => 'required|max:300',
                'tradename' => 'required|max:300',
                'address' => 'required|max:300',
                'special_contributor' => 'required|max:13',
                //'keep_accounting',
                'phone' => 'required|max:30',
                'logo' => 'mimes:jpeg,jpg,png|max:2048',
                'sign' => 'mimetypes:application/x-pkcs12,application/octet-stream|mimes:p12,bin|max:32',
                'password' => 'required_with:sign',
            ];
        } else {
            return [
                'ruc' => 'required|digits:13|unique:companies|validruc',
                'social_reason' => 'required|max:300',
                'tradename' => 'required|max:300',
                'address' => 'required|max:300',
                'special_contributor' => 'required|max:13',
                //'keep_accounting',
                'phone' => 'required|max:30',
                'logo' => 'mimes:jpeg,jpg,png|max:2048',
                'sign' => 'required|mimetypes:application/x-pkcs12,application/octet-stream|mimes:p12,bin|max:32',
                'password' => 'required',
            ];
        }
    }

    public function messages()
    {
        return [
            //'ruc.unique' => 'The :attribute has already been taken.',
            'ruc.validruc' => 'The :attribute is not valid.',
        ];
    }
}
