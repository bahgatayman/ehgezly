<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'payment_method_id' => 'required|exists:payment_methods,id',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
