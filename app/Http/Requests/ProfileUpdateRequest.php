<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'     => [
                'nullable', 
                'string', 
                'min:10',   
                'max:15',   
                'regex:/^[0-9]+$/', 
            ],
        ];
    }

    /**
     * Get the authorize rules that apply to the request.
     *
     * @return bool
     */

     public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^\d]/', '', $this->phone)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka saja.',
            'phone.min'   => 'Nomor telepon terlalu pendek, minimal harus 10 digit.',
            'phone.max'   => 'Nomor telepon terlalu panjang, maksimal 15 digit.',
        ];
    }
}
