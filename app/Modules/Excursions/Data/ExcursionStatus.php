<?php

namespace App\Modules\Excursions\Data;

use App\Modules\Shared\Enums\Concerns\HasOptions;

enum ExcursionStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Active => 'Активна',
            self::Archived => 'Архив',
        };
    }
}
