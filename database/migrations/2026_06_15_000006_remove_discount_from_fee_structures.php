<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveDiscountFromFeeStructures extends Migration
{
    public function up()
    {
        // Delete any stale discount fee structure rows (discount students use general + discount_percentage)
        DB::table('fee_structures')->where('fee_category', 'discount')->delete();

        // Tighten the enum so discount rows cannot be created via SQL either
        // (MySQL only — SQLite has no ENUM constraint to tighten in the first place)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE fee_structures MODIFY COLUMN fee_category ENUM('general','rte','coc') NOT NULL");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE fee_structures MODIFY COLUMN fee_category ENUM('general','rte','coc','discount') NOT NULL");
        }
    }
}
