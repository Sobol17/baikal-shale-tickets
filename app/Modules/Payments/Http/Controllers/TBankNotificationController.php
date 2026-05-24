<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Actions\MarkPaymentFailedAction;
use App\Modules\Payments\Actions\MarkPaymentPaidAction;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Support\TBankToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TBankNotificationController extends Controller
{
    public function __invoke(
        Request $request,
        TBankToken $tokens,
        MarkPaymentPaidAction $markPaid,
        MarkPaymentFailedAction $markFailed,
    ): Response {
        $payload = $request->all();

        if (! $tokens->verify($payload, (string) config('services.tbank.password'))) {
            return response('INVALID_TOKEN', 403);
        }

        $providerPaymentId = (string) ($payload['PaymentId'] ?? '');
        $payment = Payment::query()
            ->where('provider', 'tbank')
            ->where('provider_payment_id', $providerPaymentId)
            ->first();

        if (! $payment) {
            return response('PAYMENT_NOT_FOUND', 404);
        }

        $status = strtoupper((string) ($payload['Status'] ?? ''));

        if (in_array($status, ['CONFIRMED', 'AUTHORIZED'], true)) {
            $markPaid->handle($payment, ['notification' => $payload]);
        } elseif (in_array($status, ['REJECTED', 'CANCELED', 'DEADLINE_EXPIRED'], true)) {
            $markFailed->handle($payment, ['notification' => $payload]);
        } else {
            $payment->update([
                'raw_response' => array_replace($payment->raw_response ?? [], ['notification' => $payload]),
            ]);
        }

        return response('OK');
    }
}
