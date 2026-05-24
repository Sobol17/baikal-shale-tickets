<?php

namespace App\Modules\Payments\Console\Commands;

use App\Modules\Payments\Actions\SyncPaymentStateAction;
use App\Modules\Payments\Models\Payment;
use Illuminate\Console\Command;

class SyncPaymentStateCommand extends Command
{
    protected $signature = 'payments:sync {payment : Local payment ID}';

    protected $description = 'Sync payment state from configured payment provider.';

    public function handle(SyncPaymentStateAction $sync): int
    {
        $payment = Payment::query()->findOrFail((int) $this->argument('payment'));
        $payment = $sync->handle($payment);

        $this->info("Payment {$payment->id} status: {$payment->status->value}");

        return self::SUCCESS;
    }
}
