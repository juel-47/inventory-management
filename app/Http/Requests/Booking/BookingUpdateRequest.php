<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class BookingUpdateRequest extends FormRequest
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
            'vendor_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'integer', 'exists:sub_categories,id'],
            'child_category_id' => ['nullable', 'integer', 'exists:child_categories,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric'],
            'sale_price' => ['nullable', 'numeric'],
            'extra_cost' => ['nullable', 'numeric'],
            'min_inventory_qty' => ['nullable', 'integer'],
            'min_sale_qty' => ['nullable', 'integer'],
            'min_purchase_price' => ['nullable', 'numeric'],
            'status' => ['required', 'in:pending,complete,cancelled'],
        ];
    }
}
