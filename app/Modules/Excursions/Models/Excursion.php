<?php

namespace App\Modules\Excursions\Models;

use App\Modules\Excursions\Data\ExcursionSeason;
use App\Modules\Excursions\Data\ExcursionStatus;
use Database\Factories\ExcursionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Excursion extends Model
{
    /** @use HasFactory<ExcursionFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'season',
        'short_description',
        'full_description',
        'duration_minutes',
        'meeting_point',
        'rules',
        'cover_image',
        'adult_price_cents',
        'child_price_cents',
        'booking_starts_on',
        'booking_ends_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'adult_price_cents' => 'integer',
            'child_price_cents' => 'integer',
            'booking_starts_on' => 'date',
            'booking_ends_on' => 'date',
            'season' => ExcursionSeason::class,
            'duration_minutes' => 'integer',
            'status' => ExcursionStatus::class,
        ];
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->cover_image)) {
                return null;
            }

            if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://') || str_starts_with($this->cover_image, '/')) {
                return $this->cover_image;
            }

            return Storage::disk('public')->url($this->cover_image);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ExcursionStatus::Active->value);
    }

    protected static function newFactory(): ExcursionFactory
    {
        return ExcursionFactory::new();
    }
}
