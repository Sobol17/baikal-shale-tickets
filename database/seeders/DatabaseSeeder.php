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
                'adult_price' => 110000,
                'child_price' => 80000,
            ],
            [
                'title' => 'Прогулка к обзорным точкам Листвянки',
                'season' => ExcursionSeason::SummerAutumn,
                'description' => 'Неспешная прогулка с гидом, видами на Байкал и организацией в темпе семейного отдыха.',
                'duration' => 120,
                'adult_price' => 200000,
                'child_price' => 150000,
            ],
            [
                'title' => 'Зимний Байкал и ледовые панорамы',
                'season' => ExcursionSeason::WinterSpring,
                'description' => 'Зимняя экскурсия по живописным точкам побережья Байкала с остановками для фотографий.',
                'duration' => 180,
                'adult_price' => 300000,
                'child_price' => 220000,
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
                    'adult_price_cents' => $item['adult_price'],
                    'child_price_cents' => $item['child_price'],
                    'booking_starts_on' => now()->toDateString(),
                    'booking_ends_on' => now()->addMonths(6)->toDateString(),
                    'status' => ExcursionStatus::Active,
                ],
            );
        }
    }
}
