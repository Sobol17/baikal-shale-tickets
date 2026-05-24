<?php

namespace Database\Factories;

use App\Modules\Excursions\Data\ExcursionSlotStatus;
use App\Modules\Excursions\Models\Excursion;
use App\Modules\Excursions\Models\ExcursionSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExcursionSlot>
 */
class ExcursionSlotFactory extends Factory
{
    protected $model = ExcursionSlot::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+30 days');
        $endsAt = (clone $startsAt)->modify('+2 hours');

        return [
            'excursion_id' => Excursion::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'capacity' => fake()->numberBetween(8, 24),
            'price_cents' => fake()->randomElement([150000, 200000, 250000, 300000]),
            'status' => ExcursionSlotStatus::Active,
        ];
    }
}
