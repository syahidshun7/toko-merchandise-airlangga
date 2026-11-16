<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:10000'],
            'base_price' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'is_preorder' => ['sometimes', 'boolean'],
            'preorder_estimate' => [Rule::requiredIf(fn () => $this->boolean('is_preorder')), 'nullable', 'string', 'max:120'],
            'variants' => ['required', 'array', 'list', 'min:1', 'max:30'],
            'variants.*' => ['required', 'array:id,name,size,color,price,stock'],
            'variants.*.id' => ['nullable', 'integer', 'min:1', 'distinct'],
            'variants.*.name' => ['nullable', 'required_without:variants.*.size', 'string', 'max:100'],
            'variants.*.size' => ['nullable', 'required_with:variants.*.color', 'string', 'max:24'],
            'variants.*.color' => ['nullable', 'required_with:variants.*.size', 'string', 'max:48'],
            'variants.*.price' => ['nullable', 'integer', 'min:0', 'max:999999999999'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
            'image_order' => ['sometimes', 'array', 'max:10'],
            'image_order.*' => ['required', 'integer', 'min:0', 'max:10000'],
            'remove_images' => ['sometimes', 'array', 'max:10'],
            'remove_images.*' => ['required', 'integer', 'min:1', 'distinct'],
            'primary_image' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
