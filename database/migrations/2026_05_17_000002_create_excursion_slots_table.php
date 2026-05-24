<?php

use App\Modules\Excursions\Data\ExcursionSlotStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excursion_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excursion_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('price_cents');
            $table->string('status')->default(ExcursionSlotStatus::Draft->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursion_slots');
    }
};
