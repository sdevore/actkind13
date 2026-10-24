<?php

namespace App\Http\Requests;

use App\Enums\ActType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('update', $this->act);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'type' => ['required', Rule::enum(ActType::class)],
        ];
    }
}
