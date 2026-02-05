<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoticeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create notices');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
   public function rules()
{
    return [
        'title' => 'required',
        'description' => 'required',
        'session_id' => 'required',
        'class_id' => 'nullable|exists:school_classes,id', // 👈 add this
    ];
}

}
