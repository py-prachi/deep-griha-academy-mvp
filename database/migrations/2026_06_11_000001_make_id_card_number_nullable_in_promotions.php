<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeIdCardNumberNullableInPromotions extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE promotions MODIFY COLUMN id_card_number VARCHAR(255) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE promotions MODIFY COLUMN id_card_number VARCHAR(255) NOT NULL');
    }
}
