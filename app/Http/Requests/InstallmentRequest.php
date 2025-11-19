<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class InstallmentRequest extends FormRequest
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
        $acceptId = $this->route('installment')?->id ?? null;

        return [
            'name'  => ['required', 'string', 'max:255', 'unique:installments,name,' . $acceptId],
            'value' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        /** @var Request $request */
        $request = request();

        if ($request->is('api/*')) {
            $header = strtolower($request->header('accept-language'));
            $lan = preg_match('/^[a-z]+$/', $header) ? $header : 'en';
            app()->setLocale($lan);
        }

        return [
            'name.required'  => __('The installment name field is required.'),
            'name.unique'    => __('This installment name has already been taken.'),

            'value.required' => __('The installment value field is required.'),
            'value.numeric'  => __('The installment value must be a number.'),
            'value.min'      => __('The installment value must be at least 0.'),
        ];
    }
}
