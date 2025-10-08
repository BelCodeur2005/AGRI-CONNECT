<?php

// app/Http/Requests/PaymentRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:orange_money,mtn_momo,cash,bank_transfer',
            'phone' => 'required|string',
        ];
    }
}
