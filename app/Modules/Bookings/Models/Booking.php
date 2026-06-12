<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Payments\Models\Payment;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'excursion_id',
        'excursion_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'adult_tickets_count',
        'child_tickets_count',
        'companions',
        'seats_count',
        'amount_cents',
        'status',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'excursion_date' => 'date',
            'adult_tickets_count' => 'integer',
            'child_tickets_count' => 'integer',
            'seats_count' => 'integer',
            'amount_cents' => 'integer',
            'companions' => 'array',
            'status' => BookingStatus::class,
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function excursion(): BelongsTo
    {
        return $this->belongsTo(Excursion::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest('id')->first();
    }

    public function isPaid(): bool
    {
        return $this->status === BookingStatus::Paid;
    }

    protected static function newFactory(): BookingFactory
    {
        return BookingFactory::new();
    }
}
