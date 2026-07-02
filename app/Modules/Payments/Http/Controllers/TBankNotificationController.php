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

        if (($payload['TerminalKey'] ?? null) !== config('services.tbank.terminal_key')) {
            return response('INVALID_TERMINAL', 403);
        }

        $providerPaymentId = (string) ($payload['PaymentId'] ?? '');
        $payment = Payment::query()
            ->where('provider', 'tbank')
            ->where('provider_payment_id', $providerPaymentId)
            ->with('booking')
            ->first();

        if (! $payment) {
            return response('PAYMENT_NOT_FOUND', 404);
        }

        if (($payload['OrderId'] ?? null) !== $payment->booking->uuid) {
            return response('INVALID_ORDER', 422);
        }

        if (array_key_exists('Amount', $payload) && (int) $payload['Amount'] !== $payment->amount_cents) {
            return response('INVALID_AMOUNT', 422);
        }

        $status = strtoupper((string) ($payload['Status'] ?? ''));

        if ($this->isSuccessful($payload['Success'] ?? false)) {
            $markPaid->handle($payment, ['notification' => $payload]);
        } elseif (in_array($status, ['REJECTED', 'CANCELED', 'DEADLINE_EXPIRED', 'AUTH_FAIL'], true)) {
            $markFailed->handle($payment, ['notification' => $payload]);
        } else {
            $payment->update([
                'raw_response' => array_replace($payment->raw_response ?? [], ['notification' => $payload]),
            ]);
        }

        return response('OK');
    }

    private function isSuccessful(mixed $value): bool
    {
        return $value === true || $value === 'true' || $value === '1' || $value === 1;
    }
}
