<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpenMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'court_id' => 'required|exists:courts,id',
            'timeslot_id' => 'required|exists:timeslots,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'required_players' => 'required|integer|min:2|max:22',
        ];
    }
}
