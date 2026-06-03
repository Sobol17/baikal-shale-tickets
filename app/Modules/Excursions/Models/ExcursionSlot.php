<?php

namespace App\Modules\Excursions\Models;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Excursions\Data\ExcursionSlotStatus;
use Database\Factories\ExcursionSlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExcursionSlot extends Model
{
    /** @use HasFactory<ExcursionSlotFactory> */
    use HasFactory;

    protected $fillable = [
        'excursion_id',
        'starts_at',
        'ends_at',
        'capacity',
        'price_cents',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity' => 'integer',
            'price_cents' => 'integer',
            'status' => ExcursionSlotStatus::class,
        ];
    }

    public function excursion(): BelongsTo
    {
        return $this->belongsTo(Excursion::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', ExcursionSlotStatus::Active->value)
            ->where('starts_at', '>', now());
    }

    protected static function newFactory(): ExcursionSlotFactory
    {
        return ExcursionSlotFactory::new();
    }
}
