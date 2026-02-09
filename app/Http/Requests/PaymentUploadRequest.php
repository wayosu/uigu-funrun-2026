<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_proof' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:2048', // 2MB max
            ],
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Please select a payment method',
            'payment_proof.required' => 'Payment proof image is required',
            'payment_proof.image' => 'Payment proof must be an image file',
            'payment_proof.mimes' => 'Payment proof must be a JPEG, JPG, or PNG file',
            'payment_proof.max' => 'Payment proof file size must not exceed 2MB',
            'notes.max' => 'Notes must not exceed 500 characters',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'payment_method' => 'payment method',
            'payment_proof' => 'payment proof',
            'notes' => 'notes',
        ];
    }
}
