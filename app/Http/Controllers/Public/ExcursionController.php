<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Excursions\Actions\ListAvailableSlotsAction;
use App\Modules\Excursions\Data\ExcursionStatus;
use App\Modules\Excursions\Models\Excursion;
use Illuminate\Contracts\View\View;

class ExcursionController extends Controller
{
    public function show(Excursion $excursion, ListAvailableSlotsAction $listSlots): View
    {
        abort_unless($excursion->status === ExcursionStatus::Active, 404);

        return view('excursions.show', [
            'excursion' => $excursion,
            'slots' => $listSlots->handle($excursion),
        ]);
    }
}
