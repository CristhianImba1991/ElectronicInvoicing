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
                'role' => 'required|exists:roles,name|string',
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
                'company' => 'required|min:1',
                'company.*' => 'exists:companies,id',
                'branch' => 'required|min:1',
                'branch.*' => 'exists:branches,id',
                'emission_point' => 'required|min:1',
                'emission_point.*' => 'exists:emission_points,id',
            ];
        } else {
            return [
                'role' => 'required|exists:roles,name|string',
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
                'company' => 'required|min:1',
                'company.*' => 'exists:companies,id',
                'branch' => 'required|min:1',
                'branch.*' => 'exists:branches,id',
                'emission_point' => 'required|min:1',
                'emission_point.*' => 'exists:emission_points,id',
            ];
        }
    }
}
