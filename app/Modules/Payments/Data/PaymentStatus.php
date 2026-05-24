<?php

namespace App\Modules\Payments\Data;

use App\Modules\Shared\Enums\Concerns\HasOptions;

enum PaymentStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает оплаты',
            self::Paid => 'Оплачен',
            self::Failed => 'Ошибка',
            self::Cancelled => 'Отменен',
        };
    }
}
