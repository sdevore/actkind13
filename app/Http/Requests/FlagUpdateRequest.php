<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlagUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'act_id' => ['required', 'integer', 'exists:acts,id'],
            'comment_id' => ['required', 'integer', 'exists:comments,id'],
        ];
    }
}
