<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            DB::statement("ALTER TABLE form_data MODIFY shift VARCHAR(255)");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            DB::statement("ALTER TABLE form_data MODIFY shift ENUM('morning', 'night')");
        });
    }
};
