<?php

namespace App\Modules\Labels\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncTaskLabelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label_ids' => 'required|array',
            'label_ids.*' => 'uuid|exists:labels,id',
        ];
    }
}
