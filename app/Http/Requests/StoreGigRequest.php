<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_seller;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'packages' => 'required|array|min:1|max:3',
            'packages.*.tier' => 'required|in:basic,standard,premium',
            'packages.*.title' => 'required|string|max:255',
            'packages.*.price' => 'required|numeric|min:1',
            'packages.*.delivery_days' => 'required|integer|min:1',
            'packages.*.revisions' => 'nullable|integer|min:0',
            'packages.*.features' => 'nullable|array',
            'extras' => 'nullable|array',
            'faqs' => 'nullable|array',
            'requirements' => 'nullable|array',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'exists:skills,id',
        ];
    }
}
