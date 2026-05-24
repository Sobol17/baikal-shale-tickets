<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Bookings\Data\BookingStatus;
use App\Modules\Excursions\Models\ExcursionSlot;
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
        'excursion_slot_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'passengers',
        'seats_count',
        'amount_cents',
        'status',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'seats_count' => 'integer',
            'amount_cents' => 'integer',
            'passengers' => 'array',
            'status' => BookingStatus::class,
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ExcursionSlot::class, 'excursion_slot_id');
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
