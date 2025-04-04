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
        Schema::create('form_data', function (Blueprint $table) {
            $table->id();
            $table->enum('employee_type', ['mcls', 'agency']);
            $table->string('mcls_name')->nullable();
            $table->string('mcls_email')->nullable();
            $table->string('agency_name')->nullable();
            $table->string('agency_employee_name')->nullable();
            $table->string('site');
            $table->enum('shift', ['morning', 'night']);
            $table->string('resident_name');
            $table->date('log_date');
            $table->time('log_time');
            $table->text('adls');
            $table->text('medical');
            $table->text('behavior');
            $table->text('activities')->nullable();
            $table->text('nutrition')->nullable();
            $table->text('sleep')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_data');
    }
};
