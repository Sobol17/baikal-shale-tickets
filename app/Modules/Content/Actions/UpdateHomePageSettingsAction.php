<?php

namespace App\Modules\Content\Actions;

use App\Modules\Content\Models\HomePageSetting;
use Illuminate\Support\Facades\Storage;

class UpdateHomePageSettingsAction
{
    /**
     * @param  array{title: string, text: string, background_image: ?string, season_order?: array<int, mixed>|null}  $data
     */
    public function handle(array $data): HomePageSetting
    {
        $settings = HomePageSetting::query()->firstOrNew(['id' => HomePageSetting::SINGLETON_ID]);
        $previousBackgroundImage = $settings->background_image;

        $data['season_order'] = HomePageSetting::normalizeSeasonOrder($data['season_order'] ?? $settings->season_order);

        $settings->fill($data);
        $settings->save();

        if (
            filled($previousBackgroundImage)
            && $previousBackgroundImage !== $settings->background_image
        ) {
            Storage::disk('public')->delete($previousBackgroundImage);
        }

        return $settings;
    }
}
