<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRolloverToFeePayments extends Migration
{
    public function up()
    {
        // Add rollover payment category
        DB::statement("ALTER TABLE fee_payments MODIFY COLUMN payment_category ENUM('fee','misc','rollover') NOT NULL DEFAULT 'fee'");

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('rollover_id')->nullable()->after('payment_category');
            $table->foreign('rollover_id')->references('id')->on('fee_settlements')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['rollover_id']);
            $table->dropColumn('rollover_id');
        });

        DB::statement("ALTER TABLE fee_payments MODIFY COLUMN payment_category ENUM('fee','misc') NOT NULL DEFAULT 'fee'");
    }
}
