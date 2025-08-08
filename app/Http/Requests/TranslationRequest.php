<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
            'tag' => 'sometimes|array',
            'tag.*' => 'string'
        ];
    }
}
