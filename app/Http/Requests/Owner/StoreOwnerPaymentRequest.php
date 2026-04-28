<?php

namespace App\Http\Requests\Owner;

use App\Models\Courtowner;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOwnerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $owner = null;
        if (auth()->id()) {
            $owner = Courtowner::where('user_id', auth()->id())->first();
        }

        $maxDue = $owner?->app_due_amount;
        $maxRule = $maxDue !== null && $maxDue > 0 ? 'max:' . $maxDue : null;

        return [
            'amount' => array_filter([
                'required',
                'numeric',
                'min:1',
                $maxRule,
            ]),
            'payment_type' => 'required|in:instapay,vodafone_cash,etisalat_cash,orange_cash,we_pay',
            'receipt_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => 'المبلغ المدخل أكبر من المستحقات عليك',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Forbidden.',
            'errors' => null,
        ], 403));
    }
}
