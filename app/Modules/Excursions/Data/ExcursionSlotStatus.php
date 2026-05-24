<?php

namespace App\Modules\Excursions\Data;

use App\Modules\Shared\Enums\Concerns\HasOptions;

enum ExcursionSlotStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Active => 'Активен',
            self::Closed => 'Закрыт',
            self::Cancelled => 'Отменен',
        };
    }
}
