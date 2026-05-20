<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Requests;

use App\Modules\Analytics\DTOs\DateRange;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnalyticsRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ];
    }

    public function toDateRange(): DateRange
    {
        return DateRange::fromRequest($this->input('from'), $this->input('to'));
    }
}
