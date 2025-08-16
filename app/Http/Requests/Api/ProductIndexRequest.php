<?php

namespace App\Http\Requests\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Lunar\Models\Product;

class ProductIndexRequest extends FormRequest
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
            'maxresults' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|in:published,draft,archived',
            'page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:255',
            'collection' => 'nullable|string|exists:collections,slug',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'maxresults.max' => 'Maximum results cannot exceed 100.',
            'status.in' => 'Status must be one of: published, draft, archived.',
            'collection.exists' => 'The specified collection does not exist.',
        ];
    }

    /**
     * Get the validated data with defaults.
     */
    public function getValidatedWithDefaults(): array
    {
        $validated = $this->validated();
        
        return array_merge([
            'maxresults' => 20,
            'status' => 'published',
            'page' => 1,
            'search' => null,
            'collection' => null,
        ], $validated);
    }

    /**
     * Apply filters to the given query based on request parameters.
     */
    public function applyFilters(Builder $query): Builder
    {
        $params = $this->getValidatedWithDefaults();

        // Apply status filter
        $query->whereStatus($params['status']);

        // Apply search filter
        if ($params['search']) {
            $query->where(function ($q) use ($params) {
                $q->whereJsonContains('attribute_data->name->en', $params['search'])
                  ->orWhereJsonContains('attribute_data->description->en', $params['search']);
            });
        }

        // Apply collection filter
        if ($params['collection']) {
            $query->whereHas('collections', function ($q) use ($params) {
                $q->where('slug', $params['collection']);
            });
        }

        return $query;
    }

    /**
     * Apply pagination to the given query.
     */
    public function applyPagination(Builder $query): Builder
    {
        $params = $this->getValidatedWithDefaults();
        $offset = ($params['page'] - 1) * $params['maxresults'];

        return $query->skip($offset)->take($params['maxresults']);
    }

    /**
     * Get pagination metadata.
     */
    public function getPaginationMeta(int $totalCount): array
    {
        $params = $this->getValidatedWithDefaults();
        $offset = ($params['page'] - 1) * $params['maxresults'];

        return [
            'total' => $totalCount,
            'per_page' => $params['maxresults'],
            'current_page' => $params['page'],
            'last_page' => ceil($totalCount / $params['maxresults']),
            'from' => $offset + 1,
            'to' => min($offset + $params['maxresults'], $totalCount),
        ];
    }

    /**
     * Get applied filters for response.
     */
    public function getAppliedFilters(): array
    {
        $params = $this->getValidatedWithDefaults();

        return [
            'status' => $params['status'],
            'search' => $params['search'],
            'collection' => $params['collection'],
        ];
    }
}