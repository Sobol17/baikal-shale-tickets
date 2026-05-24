<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Excursions\Actions\ListActiveExcursionsAction;
use App\Modules\Excursions\Data\ExcursionSeason;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, ListActiveExcursionsAction $listExcursions): View
    {
        $search = $request->string('search')->trim()->toString();
        $excursions = $listExcursions->handle($search ?: null);

        return view('excursions.index', [
            'excursionsBySeason' => $excursions->groupBy(fn ($excursion): string => $excursion->season->value),
            'seasons' => ExcursionSeason::cases(),
            'search' => $search,
        ]);
    }
}
