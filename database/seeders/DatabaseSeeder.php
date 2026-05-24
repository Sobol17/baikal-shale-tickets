<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Excursions\Data\ExcursionSeason;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        $excursions = [
            [
                'title' => 'Листвянка - Большие Коты',
                'season' => ExcursionSeason::SummerAutumn,
                'description' => 'Водный маршрут по Байкалу с удобной посадкой в Листвянке и прибытием в Большие Коты.',
                'duration' => 30,
                'price' => 110000,
                'time' => [10, 0],
            ],
            [
                'title' => 'Прогулка к обзорным точкам Листвянки',
                'season' => ExcursionSeason::SummerAutumn,
                'description' => 'Неспешная прогулка с гидом, видами на Байкал и организацией в темпе семейного отдыха.',
                'duration' => 120,
                'price' => 200000,
                'time' => [14, 0],
            ],
            [
                'title' => 'Зимний Байкал и ледовые панорамы',
                'season' => ExcursionSeason::WinterSpring,
                'description' => 'Зимняя экскурсия по живописным точкам побережья Байкала с остановками для фотографий.',
                'duration' => 180,
                'price' => 300000,
                'time' => [11, 0],
            ],
        ];

        foreach ($excursions as $item) {
            $excursion = Excursion::query()->firstOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'title' => $item['title'],
                    'season' => $item['season'],
                    'short_description' => $item['description'],
                    'full_description' => 'Экскурсия знакомит гостей с природой, историей и лучшими видовыми точками маршрута. Гид встретит группу, проведет инструктаж и поможет с организационными вопросами.',
                    'duration_minutes' => $item['duration'],
                    'meeting_point' => 'Байкал Шале, Листвянка',
                    'rules' => 'Рекомендуется удобная обувь и одежда по погоде. Приходите за 10 минут до начала.',
                    'cover_image' => null,
                    'base_price_cents' => $item['price'],
                    'status' => ExcursionStatus::Active,
                ],
            );

            if ($excursion->slots()->count() > 0) {
                continue;
            }

            for ($day = 1; $day <= 5; $day++) {
                $startsAt = now()->addDays($day * 2)->setTime($item['time'][0], $item['time'][1]);

                $excursion->slots()->create([
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addMinutes($item['duration']),
                    'capacity' => 999,
                    'price_cents' => $item['price'],
                    'status' => 'active',
                ]);
            }
        }
    }
}
