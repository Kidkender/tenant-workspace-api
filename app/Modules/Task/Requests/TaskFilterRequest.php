<?php

namespace App\Modules\Task\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaskFilterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'nullable|in:todo,doing,done',
            'assigned_to' => 'nullable|string',
            'created_by' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }
}
