<?php

namespace App\Modules\Activity\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ActivityLogFilterRequest extends FormRequest
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
            "entity_id" => "nullable|string",
            "user_id" => "nullable|integer",
            "action" => "nullable|string",
            "created_at" => "nullable|string",
            "entity_type" => "nullable|string",
            "limit" => "nullable|integer"
        ];
    }
}
