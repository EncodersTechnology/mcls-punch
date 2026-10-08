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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('usertype');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });

        // ->change() would require doctrine/dbal, which this project does not depend on,
        // so a fresh migrate failed here. Only MySQL has a native ENUM; on other drivers
        // the column stays a string, which the application already treats it as.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE users MODIFY usertype "
                . "ENUM('admin','siteadmin','director','manager','supervisor','employee') NULL"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY usertype VARCHAR(255) NULL");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
