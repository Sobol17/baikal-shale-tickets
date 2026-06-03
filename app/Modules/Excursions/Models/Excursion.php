<?php

namespace App\Modules\Excursions\Models;

use App\Modules\Excursions\Data\ExcursionSeason;
use App\Modules\Excursions\Data\ExcursionStatus;
use Database\Factories\ExcursionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'base_price_cents',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'base_price_cents' => 'integer',
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

    public function slots(): HasMany
    {
        return $this->hasMany(ExcursionSlot::class);
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
