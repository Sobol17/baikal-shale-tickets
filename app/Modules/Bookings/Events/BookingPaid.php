<?php

namespace App\Modules\Bookings\Events;

use App\Modules\Bookings\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public Booking $booking) {}
}
