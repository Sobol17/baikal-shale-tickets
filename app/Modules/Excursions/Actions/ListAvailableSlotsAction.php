<?php

namespace App\Modules\Excursions\Actions;

use App\Modules\Excursions\Models\Excursion;
use App\Modules\Excursions\Models\ExcursionSlot;
use Illuminate\Database\Eloquent\Collection;

class ListAvailableSlotsAction
{
    /**
     * @return Collection<int, ExcursionSlot>
     */
    public function handle(Excursion $excursion): Collection
    {
        return $excursion->slots()
            ->available()
            ->orderBy('starts_at')
            ->get();
    }
}
