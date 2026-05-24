<?php

namespace App\Modules\Excursions\Data;

use App\Modules\Shared\Enums\Concerns\HasOptions;

enum ExcursionSeason: string
{
    use HasOptions;

    case SummerAutumn = 'summer_autumn';
    case WinterSpring = 'winter_spring';

    public function label(): string
    {
        return match ($this) {
            self::SummerAutumn => 'ЛЕТО - ОСЕНЬ',
            self::WinterSpring => 'ЗИМА - ВЕСНА',
        };
    }
}
