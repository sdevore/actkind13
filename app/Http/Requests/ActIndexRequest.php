<?php

namespace App\Http\Requests;

use App\Models\Act;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Paginator;

class ActIndexRequest extends FormRequest
{
    public const int MAX_PER_PAGE = 50;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Page number, starting at 1.
             *
             * @default 1
             */
            'page' => ['nullable', 'integer', 'min:1'],
            /**
             * Acts per page. Defaults to 12 on the guest feed and 20 on the signed-in feeds. Values above 50 are capped at 50 and values below 1 fall back to the default.
             */
            'per_page' => ['nullable', 'integer'],
        ];
    }

    /**
     * Out-of-range values fall back to the default or are capped at the maximum rather than rejected.
     */
    private function perPage(int $default): int
    {
        $perPage = $this->integer('per_page', $default);

        if ($perPage < 1) {
            return $default;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }

    /**
     * @param Builder<Act>|Relation<Act, *, *> $query
     */
    public function paginate(Builder|Relation $query, int $defaultPerPage): Paginator
    {
        return $query->simplePaginate($this->perPage($defaultPerPage));
    }
}
