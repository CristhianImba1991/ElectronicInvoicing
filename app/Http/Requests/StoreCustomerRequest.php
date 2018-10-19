<?php

namespace ElectronicInvoicing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
                'company' => 'required|exists:companies,id',
                'identification_type' => 'required|exists:identification_types,id',
                'identification' => 'required|exists:customers,identification|max:20',
                'social_reason' => 'required|max:300',
                'address' => 'required|max:300',
                'phone' => 'required|max:30',
                'email' => 'required|max:300',
            ];
        } else {
            return [
                'company' => 'required|exists:companies,id',
                'identification_type' => 'required|exists:identification_types,id',
                'identification' => 'required|max:20',
                'social_reason' => 'required|max:300',
                'address' => 'required|max:300',
                'phone' => 'required|max:30',
                'email' => 'required|max:300',
            ];
        }
    }
}
