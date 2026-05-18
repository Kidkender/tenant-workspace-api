<?php

namespace App\Modules\Labels\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }
}
