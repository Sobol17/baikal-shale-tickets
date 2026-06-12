<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentResultController extends Controller
{
    public function success(Request $request): View
    {
        $booking = $this->resolveBooking($request);

        return view('payments.success', ['booking' => $booking]);
    }

    public function fail(Request $request): View
    {
        $booking = $this->resolveBooking($request);

        return view('payments.fail', ['booking' => $booking]);
    }

    private function resolveBooking(Request $request): ?Booking
    {
        $uuid = $request->string('booking')->toString();

        if ($uuid === '') {
            return null;
        }

        return Booking::query()
            ->where('uuid', $uuid)
            ->with('excursion')
            ->first();
    }
}
