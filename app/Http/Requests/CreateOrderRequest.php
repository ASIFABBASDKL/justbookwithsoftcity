<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_buyer;
    }

    public function rules(): array
    {
        return [
            'gig_package_id' => 'required|exists:gig_packages,id',
            'extra_ids' => 'nullable|array',
            'extra_ids.*' => 'exists:gig_extras,id',
        ];
    }
}
