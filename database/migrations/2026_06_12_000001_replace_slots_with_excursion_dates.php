<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->renameColumn('base_price_cents', 'adult_price_cents');
            $table->unsignedInteger('child_price_cents')->nullable()->after('adult_price_cents');
            $table->date('booking_starts_on')->nullable()->after('child_price_cents');
            $table->date('booking_ends_on')->nullable()->after('booking_starts_on');
        });

        DB::table('excursions')
            ->whereNull('child_price_cents')
            ->update([
                'child_price_cents' => DB::raw('adult_price_cents'),
                'booking_starts_on' => now()->toDateString(),
                'booking_ends_on' => now()->addYear()->toDateString(),
            ]);

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('excursion_id')->nullable()->after('uuid')->constrained()->restrictOnDelete();
            $table->date('excursion_date')->nullable()->after('excursion_id');
            $table->unsignedInteger('adult_tickets_count')->default(1)->after('customer_phone');
            $table->unsignedInteger('child_tickets_count')->default(0)->after('adult_tickets_count');
            $table->json('companions')->nullable()->after('child_tickets_count');
        });

        DB::table('bookings')
            ->leftJoin('excursion_slots', 'bookings.excursion_slot_id', '=', 'excursion_slots.id')
            ->select([
                'bookings.id',
                'bookings.passengers',
                'bookings.seats_count',
                'excursion_slots.excursion_id',
                'excursion_slots.starts_at',
            ])
            ->orderBy('bookings.id')
            ->get()
            ->each(function (object $booking): void {
                $passengers = json_decode((string) $booking->passengers, true);
                $passengers = is_array($passengers) ? $passengers : [];

                $adultTickets = 0;
                $childTickets = 0;

                foreach ($passengers as $passenger) {
                    if (($passenger['ticket_type'] ?? 'adult') === 'child') {
                        $childTickets++;
                    } else {
                        $adultTickets++;
                    }
                }

                if ($adultTickets + $childTickets === 0) {
                    $adultTickets = max(1, (int) $booking->seats_count);
                }

                $companions = collect($passengers)
                    ->skip(1)
                    ->map(fn (array $passenger): array => [
                        'last_name' => (string) ($passenger['last_name'] ?? ''),
                        'first_name' => (string) ($passenger['first_name'] ?? ''),
                    ])
                    ->values()
                    ->all();

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update([
                        'excursion_id' => $booking->excursion_id,
                        'excursion_date' => $booking->starts_at
                            ? Carbon::parse($booking->starts_at)->toDateString()
                            : now()->toDateString(),
                        'adult_tickets_count' => $adultTickets,
                        'child_tickets_count' => $childTickets,
                        'companions' => $companions === [] ? null : json_encode($companions),
                    ]);
            });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['excursion_slot_id']);
            $table->dropIndex(['excursion_slot_id', 'status']);
            $table->dropColumn(['excursion_slot_id', 'passengers']);
            $table->index(['excursion_id', 'excursion_date', 'status']);
        });

        Schema::dropIfExists('excursion_slots');
    }

    public function down(): void
    {
        Schema::create('excursion_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excursion_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('price_cents');
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('excursion_slot_id')->nullable()->after('uuid')->constrained()->nullOnDelete();
            $table->json('passengers')->nullable()->after('customer_phone');
            $table->dropIndex(['excursion_id', 'excursion_date', 'status']);
            $table->dropForeign(['excursion_id']);
            $table->dropColumn([
                'excursion_id',
                'excursion_date',
                'adult_tickets_count',
                'child_tickets_count',
                'companions',
            ]);
        });

        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn(['child_price_cents', 'booking_starts_on', 'booking_ends_on']);
            $table->renameColumn('adult_price_cents', 'base_price_cents');
        });
    }
};
