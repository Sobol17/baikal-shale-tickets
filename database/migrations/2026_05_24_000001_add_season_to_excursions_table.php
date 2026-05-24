<?php

use App\Modules\Excursions\Data\ExcursionSeason;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table
                ->string('season')
                ->default(ExcursionSeason::SummerAutumn->value)
                ->after('slug')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn('season');
        });
    }
};
