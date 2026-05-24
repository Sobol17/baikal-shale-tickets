<?php

namespace App\Modules\Excursions\Actions;

use App\Modules\Excursions\Models\Excursion;
use Illuminate\Database\Eloquent\Collection;

class ListActiveExcursionsAction
{
    /**
     * @return Collection<int, Excursion>
     */
    public function handle(?string $search = null): Collection
    {
        return Excursion::query()
            ->active()
            ->with(['slots' => fn ($query) => $query->available()->orderBy('starts_at')])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('full_description', 'like', "%{$search}%");
            }))
            ->orderBy('season')
            ->orderBy('title')
            ->get();
    }
}
