<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $number = $this->input('whatsapp_number');

        if (is_string($number)) {
            $number = preg_replace('/[\s().-]+/', '', $number);

            if (str_starts_with($number, '+')) {
                $number = substr($number, 1);
            } elseif (str_starts_with($number, '08')) {
                $number = '62'.substr($number, 1);
            }

            $this->merge(['whatsapp_number' => $number]);
        }
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:150'],
            'school_name' => ['required', 'string', 'max:150'],
            'whatsapp_number' => ['nullable', 'string', 'regex:/^[1-9][0-9]{7,14}$/D'],
            'address' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }
}
