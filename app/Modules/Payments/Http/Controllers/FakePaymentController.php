<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Actions\MarkPaymentPaidAction;
use App\Modules\Payments\Models\Payment;
use Illuminate\Http\RedirectResponse;

class FakePaymentController extends Controller
{
    public function paid(Payment $payment, MarkPaymentPaidAction $markPaid): RedirectResponse
    {
        $markPaid->handle($payment, ['fake' => ['Status' => 'CONFIRMED']]);

        return redirect()->route('payment.success', ['booking' => $payment->booking->uuid]);
    }
}
