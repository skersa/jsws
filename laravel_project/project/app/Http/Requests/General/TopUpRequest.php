<?php

namespace App\Http\Requests\General;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:10',
                'max:50000',
            ],
            'payment_method' => [
                'required',
                'in:credit_card,bank_transfer,papara',
            ],
            'card_holder'   => ['required_if:payment_method,credit_card', 'nullable', 'string', 'max:100'],
            'card_number'   => ['required_if:payment_method,credit_card', 'nullable', 'digits:16'],
            'card_expiry'   => ['required_if:payment_method,credit_card', 'nullable', 'string', 'regex:/^\d{2}\/\d{2}$/'],
            'card_cvv'      => ['required_if:payment_method,credit_card', 'nullable', 'digits_between:3,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required'        => 'Yükleme tutarı zorunludur.',
            'amount.min'             => 'Minimum yükleme tutarı 10 ₺\'dir.',
            'amount.max'             => 'Tek seferinde maksimum 50.000 ₺ yüklenebilir.',
            'payment_method.required'=> 'Ödeme yöntemi seçiniz.',
            'payment_method.in'      => 'Geçersiz ödeme yöntemi.',
            'card_holder.required_if'=> 'Kart sahibi adı zorunludur.',
            'card_number.required_if'=> 'Kart numarası zorunludur.',
            'card_number.digits'     => 'Kart numarası 16 haneli olmalıdır.',
            'card_expiry.required_if'=> 'Son kullanma tarihi zorunludur.',
            'card_expiry.regex'      => 'Son kullanma tarihi AA/YY formatında olmalıdır.',
            'card_cvv.required_if'   => 'CVV zorunludur.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->card_number) {
            $this->merge([
                'card_number' => preg_replace('/\s+/', '', $this->card_number),
            ]);
        }
    }
}
