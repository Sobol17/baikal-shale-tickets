<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Excursions\Actions\ListActiveExcursionsAction;
use App\Modules\Excursions\Actions\ListAvailableSlotsAction;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcursionController extends Controller
{
    public function search(Request $request, ListActiveExcursionsAction $listExcursions): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();

        if ($search === '') {
            return response()->json([
                'items' => [],
            ]);
        }

        $items = $listExcursions->handle($search)
            ->take(6)
            ->map(fn (Excursion $excursion): array => [
                'title' => $excursion->title,
                'description' => $excursion->short_description,
                'duration' => "{$excursion->duration_minutes} мин.",
                'price' => 'от '.number_format($excursion->base_price_cents / 100, 0, ',', ' ').' ₽',
                'nextSlot' => $excursion->slots->first()?->starts_at?->format('d.m H:i'),
                'image' => $excursion->cover_image_url,
                'url' => route('excursions.show', $excursion),
            ])
            ->values();

        return response()->json([
            'items' => $items,
        ]);
    }

    public function show(Excursion $excursion, ListAvailableSlotsAction $listSlots): View
    {
        abort_unless($excursion->status === ExcursionStatus::Active, 404);

        return view('excursions.show', [
            'excursion' => $excursion,
            'slots' => $listSlots->handle($excursion),
        ]);
    }
}
