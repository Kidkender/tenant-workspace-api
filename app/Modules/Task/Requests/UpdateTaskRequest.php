<?php

namespace App\Modules\Task\Requests;

use App\Common\Enumeration\TaskPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'title' => 'sometimes|string',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,doing,completed',
            'due_date' => 'nullable|date',
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
        ];
    }
}
