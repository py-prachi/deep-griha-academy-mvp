<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPaymentCategoryToFeePayments extends Migration
{
    public function up()
    {
        // Step 1: Add payment_category column to fee_payments
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->enum('payment_category', ['fee', 'misc'])->default('fee')->after('is_internal_transfer');
        });

        // Step 2: Rebuild fee_line_items description enum with new values
        // MySQL only — SQLite does not support MODIFY COLUMN / ENUM
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE fee_line_items
                MODIFY COLUMN description ENUM(
                    'admission_fee',
                    'tuition_fee',
                    'transport_charges',
                    'transfer_certificate',
                    'bonafide_certificate',
                    'other_fee',
                    'uniform',
                    'notebooks',
                    'stationery',
                    'sports',
                    'other_misc'
                ) NOT NULL
            ");
        }
    }

    public function down()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn('payment_category');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE fee_line_items
                MODIFY COLUMN description ENUM(
                    'admission_fee',
                    'tuition_fee',
                    'other_fee',
                    'transfer_certificate',
                    'bonafide_certificate',
                    'transport_charges',
                    'stationery',
                    'uniform',
                    'sports',
                    'notebooks'
                ) NOT NULL
            ");
        }
    }
}
