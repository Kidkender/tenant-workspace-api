<?php

declare(strict_types=1);

namespace App\Modules\Analytics\DTOs;

use Carbon\CarbonImmutable;

final readonly class DateRange
{
    public function __construct(
        public CarbonImmutable $from,
        public CarbonImmutable $to,
    ) {}

    public static function fromRequest(?string $from, ?string $to, int $defaultDays = 30): self
    {
        $parsedTo = $to ? CarbonImmutable::parse($to)->endOfDay() : CarbonImmutable::now()->endOfDay();
        $parsedFrom = $from ? CarbonImmutable::parse($from)->startOfDay() : $parsedTo->subDays($defaultDays)->startOfDay();

        return new self($parsedFrom, $parsedTo);
    }
}
