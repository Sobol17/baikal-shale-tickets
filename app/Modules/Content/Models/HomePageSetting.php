<?php

namespace App\Modules\Content\Models;

use App\Modules\Excursions\Data\ExcursionSeason;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomePageSetting extends Model
{
    public const int SINGLETON_ID = 1;

    public const string DEFAULT_TITLE = 'Экскурсии Байкал Шале';

    public const string DEFAULT_TEXT = 'Выберите маршрут, дату экскурсии и оформите билеты без регистрации.';

    protected $fillable = [
        'title',
        'text',
        'background_image',
        'season_order',
    ];

    /**
     * @return array{title: string, text: string, background_image: null}
     */
    public static function defaults(): array
    {
        return [
            'title' => self::DEFAULT_TITLE,
            'text' => self::DEFAULT_TEXT,
            'background_image' => null,
        ];
    }

    public static function current(): self
    {
        return self::query()->find(self::SINGLETON_ID) ?? new self(self::defaults());
    }

    /**
     * @return array<int, string>
     */
    public static function defaultSeasonOrder(): array
    {
        return array_column(ExcursionSeason::cases(), 'value');
    }

    /**
     * Оставляет только известные подборки, убирает дубли и дописывает
     * недостающие в порядке объявления enum.
     *
     * @param  array<int, mixed>|null  $values
     * @return array<int, string>
     */
    public static function normalizeSeasonOrder(?array $values): array
    {
        $order = collect($values ?? [])
            ->map(fn (mixed $value): ?ExcursionSeason => is_string($value) ? ExcursionSeason::tryFrom($value) : null)
            ->filter()
            ->map(fn (ExcursionSeason $season): string => $season->value)
            ->unique();

        return $order
            ->merge(self::defaultSeasonOrder())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, ExcursionSeason>
     */
    public function orderedSeasons(): array
    {
        return array_map(
            fn (string $value): ExcursionSeason => ExcursionSeason::from($value),
            self::normalizeSeasonOrder($this->season_order),
        );
    }

    protected function backgroundImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->background_image)) {
                return null;
            }

            return Storage::disk('public')->url($this->background_image);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'season_order' => 'array',
        ];
    }
}
