<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Content\Models\HomePageSetting;
use App\Modules\Excursions\Actions\ListActiveExcursionsAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, ListActiveExcursionsAction $listExcursions): View
    {
        $search = $request->string('search')->trim()->toString();
        $excursions = $listExcursions->handle($search ?: null);
        $homePageSettings = HomePageSetting::current();

        return view('excursions.index', [
            'excursionsBySeason' => $excursions->groupBy(fn ($excursion): string => $excursion->season->value),
            'homePageSettings' => $homePageSettings,
            'seasons' => $homePageSettings->orderedSeasons(),
            'search' => $search,
        ]);
    }
}
