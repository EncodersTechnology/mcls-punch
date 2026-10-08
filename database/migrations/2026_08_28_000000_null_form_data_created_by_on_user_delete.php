<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * form_data.created_by was declared without an onDelete rule, so MySQL defaulted it to
     * RESTRICT. Deleting any user who had ever filed a log therefore raised a foreign key
     * violation and surfaced as a 500 -- both from Access Management and from the profile
     * page's "Delete Account". The logs are clinical records and must outlive the account,
     * so the reference is nulled rather than cascaded.
     */
    public function up(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('form_data', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
