<?php

namespace Database\Factories;

use App\Modules\Excursions\Data\ExcursionSeason;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Excursion>
 */
class ExcursionFactory extends Factory
{
    protected $model = Excursion::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'season' => fake()->randomElement(ExcursionSeason::cases()),
            'short_description' => fake()->sentence(16),
            'full_description' => fake()->paragraphs(3, true),
            'duration_minutes' => fake()->randomElement([90, 120, 150, 180]),
            'meeting_point' => fake()->address(),
            'rules' => fake()->paragraph(),
            'cover_image' => null,
            'base_price_cents' => fake()->randomElement([150000, 200000, 250000, 300000]),
            'status' => ExcursionStatus::Active,
        ];
    }
}
