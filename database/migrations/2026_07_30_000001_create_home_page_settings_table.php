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
        Schema::create('home_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('text', 500);
            $table->string('background_image')->nullable();
            $table->timestamps();
        });

        DB::table('home_page_settings')->insert([
            'id' => HomePageSetting::SINGLETON_ID,
            ...HomePageSetting::defaults(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_page_settings');
    }
};
