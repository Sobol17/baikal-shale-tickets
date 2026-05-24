<?php

use App\Modules\Excursions\Data\ExcursionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excursions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500);
            $table->text('full_description');
            $table->unsignedInteger('duration_minutes');
            $table->string('meeting_point');
            $table->text('rules')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('base_price_cents');
            $table->string('status')->default(ExcursionStatus::Draft->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excursions');
    }
};
