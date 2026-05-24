<?php

namespace App\Modules\Bookings\Data;

use App\Modules\Shared\Enums\Concerns\HasOptions;

enum BookingStatus: string
{
    use HasOptions;

    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Ожидает оплаты',
            self::Paid => 'Оплачено',
            self::Cancelled => 'Отменено',
            self::Failed => 'Ошибка оплаты',
        };
    }
}
