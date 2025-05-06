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
        Schema::create('xwalk_site_checklist_type', function (Blueprint $table) {
            $table->id();
            $table->string('checklist_type');
            $table->string('group_name');
            $table->string('task_name');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('site_checklist_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_checklist_id');
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->foreign('site_checklist_id')->references('id')->on('xwalk_site_checklist_type')->onDelete('cascade');
            $table->boolean('sun_enabled_bool')->default(1);
            $table->boolean('mon_enabled_bool')->default(1);
            $table->boolean('tue_enabled_bool')->default(1);
            $table->boolean('wed_enabled_bool')->default(1);
            $table->boolean('thu_enabled_bool')->default(1);
            $table->boolean('fri_enabled_bool')->default(1);
            $table->boolean('sat_enabled_bool')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('site_checklist_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('site_checklist_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('site_checklist_id')->references('id')->on('xwalk_site_checklist_type')->onDelete('cascade');
            $table->boolean('sun_bool')->nullable();
            $table->boolean('mon_bool')->nullable();
            $table->boolean('tue_bool')->nullable();
            $table->boolean('wed_bool')->nullable();
            $table->boolean('thu_bool')->nullable();
            $table->boolean('fri_bool')->nullable();
            $table->boolean('sat_bool')->nullable();
            $table->string('temp_value')->nullable();
            $table->datetime('log_date_time');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->integer('is_deleted')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_checklists_data');
        Schema::dropIfExists('site_checklist_settings');
        Schema::dropIfExists('xwalk_site_checklist_type');
    }
};
