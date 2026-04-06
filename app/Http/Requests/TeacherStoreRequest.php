<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create users');
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'gender'        => 'required|string',
            'nationality'   => 'nullable|string',
            'phone'         => 'required|string',
            'address'       => 'nullable|string',
            'address2'      => 'nullable|string',
            'city'          => 'nullable|string',
            'zip'           => 'nullable|string',
            'photo'         => 'nullable|string',
        ];
    }
}
