<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:FIVE_A_SIDE,SIX_A_SIDE,SEVEN_A_SIDE,ELEVEN_A_SIDE',
            'surface_type' => 'required|in:grass,artificial_turf,cement',
            'price_per_hour' => 'required|numeric|min:0',
        ];
    }
}
