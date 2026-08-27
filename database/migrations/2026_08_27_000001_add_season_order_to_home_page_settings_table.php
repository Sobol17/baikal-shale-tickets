<?php

use App\Modules\Content\Models\HomePageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_page_settings', function (Blueprint $table) {
            $table->json('season_order')->nullable()->after('background_image');
        });

        DB::table('home_page_settings')->update([
            'season_order' => json_encode(HomePageSetting::defaultSeasonOrder()),
        ]);
    }

    public function down(): void
    {
        Schema::table('home_page_settings', function (Blueprint $table) {
            $table->dropColumn('season_order');
        });
    }
};
