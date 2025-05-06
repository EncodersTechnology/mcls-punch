<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_checklist_data', function (Blueprint $table) {
            $table->date('week_start')->nullable();
            $table->date('week_end')->nullable();
            $table->json('day_date_map')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_checklist_data', function (Blueprint $table) {
            //
        });
    }
};
