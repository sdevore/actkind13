<?php

namespace App\Http\Requests;

use App\Models\Appreciate;
use Illuminate\Foundation\Http\FormRequest;

class AppreciateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', [Appreciate::class, $this->route('act')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
