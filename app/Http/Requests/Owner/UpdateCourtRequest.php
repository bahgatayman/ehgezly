<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'type' => 'sometimes|nullable|in:FIVE_A_SIDE,SIX_A_SIDE,SEVEN_A_SIDE,ELEVEN_A_SIDE',
            'surface_type' => 'sometimes|nullable|in:grass,artificial_turf,cement',
            'price_per_hour' => 'sometimes|nullable|numeric|min:0',
        ];
    }
}
