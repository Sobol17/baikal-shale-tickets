<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Bookings\Events\BookingPaid;
use App\Modules\Notifications\Mail\BookingPaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingPaidEmail implements ShouldQueue
{
    public function handle(BookingPaid $event): void
    {
        $booking = $event->booking->loadMissing('excursion');

        Mail::to($booking->customer_email)->send(new BookingPaidMail($booking));
    }
}
