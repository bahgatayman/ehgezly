<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'type' => 'required|in:instapay,vodafone_cash,etisalat_cash,orange_cash,we_pay',
                'identifier' => 'required|string',
                'is_active' => 'nullable|boolean',
            ];
        }

        return [
            'type' => 'sometimes|nullable|in:instapay,vodafone_cash,etisalat_cash,orange_cash,we_pay',
            'identifier' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|nullable|boolean',
        ];
    }
}
