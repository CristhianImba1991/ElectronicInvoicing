<?php

namespace ElectronicInvoicing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
                'role' => 'nullable|exists:roles,name|string',
                'name' => 'required|max:255',
                //'email' => 'required|email|max:255|unique:users',
                //'password' => 'required|min:6|confirmed',
                'company' => 'nullable|min:1',
                'company.*' => 'exists:companies,id',
                'branch' => 'nullable|min:1',
                'branch.*' => 'exists:branches,id',
                'emission_point' => 'nullable|min:1',
                'emission_point.*' => 'exists:emission_points,id',
            ];
        } else {
            return [
                'role' => 'required|exists:roles,name|string',
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
                'company' => 'required_unless:role,admin|min:1',
                'company.*' => 'exists:companies,id',
                'branch' => 'required_unless:role,admin,owner|min:1',
                'branch.*' => 'exists:branches,id',
                'emission_point' => 'required_unless:role,admin,owner|min:1',
                'emission_point.*' => 'exists:emission_points,id',
            ];
        }
    }

    public function messages()
    {
        return [
            'company.required_unless' => 'The :attribute field is required.',
            'branch.required_unless' => 'The :attribute field is required.',
            'emission_point.required_unless' => 'The :attribute field is required.',
        ];
    }
}
