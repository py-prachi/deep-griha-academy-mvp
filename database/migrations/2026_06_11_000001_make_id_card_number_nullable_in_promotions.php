<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeIdCardNumberNullableInPromotions extends Migration
{
    public function up()
    {
        // MySQL only — SQLite has no MODIFY COLUMN and has no NOT NULL constraint
        // to relax here anyway (its "schema-less" typing already accepts NULL).
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE promotions MODIFY COLUMN id_card_number VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE promotions MODIFY COLUMN id_card_number VARCHAR(255) NOT NULL');
        }
    }
}
