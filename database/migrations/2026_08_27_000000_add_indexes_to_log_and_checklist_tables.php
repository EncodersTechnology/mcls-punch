<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every log and checklist screen filters on these columns, and none of them were
     * indexed. site_users also had no uniqueness guarantee, so a user could be attached
     * to the same site twice.
     */
    public function up(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->index(['site_id', 'log_date'], 'form_data_site_id_log_date_index');
            $table->index('log_date', 'form_data_log_date_index');
        });

        Schema::table('site_checklist_data', function (Blueprint $table) {
            $table->index(['site_id', 'log_date_time'], 'scd_site_id_log_date_time_index');
            $table->index(['week_start', 'week_end'], 'scd_week_start_week_end_index');
        });

        Schema::table('site_checklist_settings', function (Blueprint $table) {
            $table->index(['site_id', 'site_checklist_id'], 'scs_site_id_checklist_id_index');
        });

        // Collapse any pre-existing duplicate assignments before enforcing uniqueness,
        // keeping the earliest row for each pair.
        $duplicates = DB::table('site_users')
            ->select('user_id', 'site_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'site_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('site_users')
                ->where('user_id', $duplicate->user_id)
                ->where('site_id', $duplicate->site_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('site_users', function (Blueprint $table) {
            $table->unique(['user_id', 'site_id'], 'site_users_user_id_site_id_unique');
        });
    }

    /**
     * Each composite index below leads with a foreign-key column, and MySQL responds by
     * dropping the auto-created index behind that constraint as redundant. That leaves the
     * new index as the only one serving the foreign key, so it cannot be dropped until the
     * constraint is lifted. Every block here therefore drops the key, drops the index, and
     * restores the key with its original delete rule.
     */
    public function down(): void
    {
        Schema::table('site_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('site_users_user_id_site_id_unique');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('site_checklist_settings', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropIndex('scs_site_id_checklist_id_index');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        Schema::table('site_checklist_data', function (Blueprint $table) {
            $table->dropIndex('scd_week_start_week_end_index');
            $table->dropForeign(['site_id']);
            $table->dropIndex('scd_site_id_log_date_time_index');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        Schema::table('form_data', function (Blueprint $table) {
            $table->dropIndex('form_data_log_date_index');
            $table->dropForeign(['site_id']);
            $table->dropIndex('form_data_site_id_log_date_index');
            $table->foreign('site_id')->references('id')->on('sites');
        });
    }
};
