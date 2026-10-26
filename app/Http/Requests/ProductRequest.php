<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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

        $additionThumbnail = $this->product?->medias?->isNotEmpty() ? 'nullable' : 'nullable';
        $thumbnail = $this->product?->media ? 'nullable' : 'required';

        // fields the dashboard form requires; the seller app (api) keeps them optional
        $dashboardRequired = 'nullable';

        if ($this->is('api/*')) {
            $additionThumbnail = 'nullable';
        } else {
            $dashboardRequired = 'required';
        }

            // 'quantity' => 'required|integer|min:0',
        return [
            'name' => 'required|string|max:191',
            'name_ar' => "$dashboardRequired|string|max:191",
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'branches' => 'required|array',
            'branches.*' => 'nullable|integer|min:0',
            'short_description' => 'required|string|max:191',
            'short_description_ar' => 'nullable|string|max:191',
            'category' => 'required|exists:categories,id',
            'sub_category' => 'nullable|array|exists:sub_categories,id',
            'brand' => 'nullable|exists:brands,id',
            'code' => ['required', 'string', 'max:25', 'regex:/^[A-Za-z0-9-]+$/'],
            'color' => 'nullable|array',
            'color.*.name' => 'nullable|string|max:255',
            'color.*.code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color.*.price' => 'nullable|numeric|min:0',
            'size' => 'nullable|array',
            'size.*.id' => 'nullable|exists:sizes,id',
            'size.*.price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|exists:units,id',
            'buy_price' => "$dashboardRequired|numeric|min:0",
            'price' => 'required|numeric|min:0',
            // discount price = discounted carton price, so it must stay under the carton price
            'discount_price' => 'nullable|numeric|min:0|lt:'.$this->cartonPriceLimit(),
            'min_order_quantity' => 'nullable|integer|min:1',
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:200',
            'thumbnail' => "$thumbnail|image|mimes:png,jpg,jpeg,webp|max:2048",
            'additionThumbnail' => "$additionThumbnail|array",
            'additionThumbnail.*' => 'image|mimes:png,jpg,jpeg,webp|max:2048',

            'previousThumbnail' => 'nullable|array',
            'previousThumbnail.*.id' => 'nullable|exists:media,id',
            'previousThumbnail.*.file' => 'nullable|file|mimes:png,jpg,jpeg,webp|max:2048',
            
            'carton_contents' => 'nullable|string|max:1000',
            'carton_units_count' => 'nullable|integer|min:1',
            'carton_price' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * The carton price the discount is compared with: the entered carton price, or piece price x pack.
     */
    private function cartonPriceLimit(): float
    {
        if ((float) $this->carton_price > 0) {
            return (float) $this->carton_price;
        }

        return (float) $this->price * max(1, (int) $this->carton_units_count);
    }

    public function messages(): array
    {
        $request = request();
        if ($request->is('api/*')) {
            $header = strtolower($request->header('accept-language'));
            $lan = (preg_match('/^[a-z]+$/', $header)) ? $header : 'en';
            app()->setLocale($lan);
        }
            // 'quantity.required' => __('The quantity field is required.'),
            // 'quantity.integer' => __('The quantity must be an integer.'),
            // 'quantity.min' => __('The quantity must be at least 0.'),

        return [
            'name.required' => __('The name field is required.'),
            'name.max' => __('The name may not be greater than 191 characters.'),
            'name_ar.required' => __('The Arabic product name is required.'),
            'name_ar.max' => __('The Arabic product name may not be greater than 191 characters.'),
            'short_description_ar.max' => __('The Arabic short description may not be greater than 191 characters.'),
            'branches.*.integer' => __('The branch quantity must be a whole number.'),
            'branches.*.min' => __('The branch quantity must be at least 0.'),
            'color.*.code' => __('The color code must be a valid hex color.'),
            'color.*.price' => __('The color extra price must be a number of at least 0.'),
            'buy_price.required' => __('The buying price is required.'),
            'buy_price.numeric' => __('The buying price must be a number.'),
            'description.required' => __('The description field is required.'),
            'branches.required' => __('Please select at least one branch and enter its quantity.'),
            'short_description.required' => __('The short description field is required.'),
            'short_description.max' => __('The short description may not be greater than 191 characters.'),
            'category.required' => __('The category field is required.'),
            'category.exists' => __('The selected category is invalid.'),
            'code.required' => __('The code field is required.'),
            'code.unique' => __('The code has already been taken.'),
            'code.regex' => __('The code may only contain English letters, numbers and dashes.'),
            'code.max' => __('The code may not be greater than 25 characters.'),
            'price.required' => __('The price field is required.'),
            'price.numeric' => __('The price must be a number.'),
            'price.min' => __('The price must be at least 0.'),
            'discount_price.numeric' => __('The discount price must be a number.'),
            'discount_price.min' => __('The discount price must be at least 0.'),
            'discount_price.lt' => __('The discounted carton price must be less than the carton price.'),
            'min_order_quantity.required' => __('The min order quantity field is required.'),
            'min_order_quantity.integer' => __('The min order quantity must be an integer.'),
            'min_order_quantity.min' => __('The min order quantity must be at least 1.'),
            'thumbnail.required' => __('The thumbnail field is required.'),
            'thumbnail.image' => __('The thumbnail must be an image.'),
            'thumbnail.mimes' => __('The thumbnail must be a file of type: png, jpg, jpeg, webp.'),
            'thumbnail.max' => __('The thumbnail may not be greater than 2048 kilobytes.'),
            'additionThumbnail.required' => __('The addition thumbnail field is required.'),
            'additionThumbnail.image' => __('The addition thumbnail must be an image.'),
            'additionThumbnail.mimes' => __('The addition thumbnail must be a file of type: png, jpg, jpeg, webp.'),
            'additionThumbnail.max' => __('The addition thumbnail may not be greater than 2048 kilobytes.'),
            'carton_contents.max' => __('The carton contents may not be greater than 1000 characters.'),
            'carton_units_count.integer' => __('The carton pack must be a whole number.'),
            'carton_units_count.min' => __('The carton pack must be at least 1.'),
            'carton_price.numeric' => __('The carton price must be a number.'),
        ];
    }
}