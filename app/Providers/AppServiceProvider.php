<?php

namespace App\Providers;

use App\Modules\Bookings\Events\BookingPaid;
use App\Modules\Notifications\Listeners\SendBookingPaidEmail;
use App\Modules\Payments\Contracts\PaymentGateway;
use App\Modules\Payments\Gateways\FakePaymentGateway;
use App\Modules\Payments\Gateways\TBankPaymentGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            return match (config('services.payments.default', 'fake')) {
                'tbank' => $this->app->make(TBankPaymentGateway::class),
                default => $this->app->make(FakePaymentGateway::class),
            };
        });
    }

    public function boot(): void
    {
        Event::listen(BookingPaid::class, SendBookingPaidEmail::class);
    }
}
