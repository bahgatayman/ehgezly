<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hours' => 'required|array',
            'hours.*.day_of_week' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'hours.*.open_time' => 'required_if:hours.*.is_open,true|date_format:H:i',
            'hours.*.close_time' => 'required_if:hours.*.is_open,true|date_format:H:i|after:hours.*.open_time',
            'hours.*.is_open' => 'required|boolean',
        ];
    }
}
