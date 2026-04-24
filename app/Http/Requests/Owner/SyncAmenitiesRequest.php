<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class SyncAmenitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amenity_ids' => 'required|array',
            'amenity_ids.*' => 'exists:amenities,id',
        ];
    }
}
