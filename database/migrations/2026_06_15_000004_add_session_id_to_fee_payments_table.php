<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSessionIdToFeePaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->after('student_user_id');
            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('set null');
        });

        // Backfill all existing payments to session 1 (2025-2026)
        DB::table('fee_payments')->whereNull('session_id')->update(['session_id' => 1]);
    }

    public function down()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');
        });
    }
}
