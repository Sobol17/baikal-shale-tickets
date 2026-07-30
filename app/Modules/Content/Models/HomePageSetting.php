<?php

namespace App\Modules\Content\Models;

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

    protected function backgroundImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->background_image)) {
                return null;
            }

            return Storage::disk('public')->url($this->background_image);
        });
    }
}
