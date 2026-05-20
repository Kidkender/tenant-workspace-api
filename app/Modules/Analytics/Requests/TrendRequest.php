<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class TrendRequest extends AnalyticsRangeRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'granularity' => 'nullable|in:day,week',
            'metric' => 'nullable|in:created,completed,both',
        ];
    }

    public function granularity(): string
    {
        return $this->input('granularity', 'day');
    }

    public function metric(): string
    {
        return $this->input('metric', 'both');
    }
}
