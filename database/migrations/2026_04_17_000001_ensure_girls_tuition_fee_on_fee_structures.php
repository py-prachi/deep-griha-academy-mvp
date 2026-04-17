<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureGirlsTuitionFeeOnFeeStructures extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('fee_structures', 'girls_tuition_fee')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->decimal('girls_tuition_fee', 10, 2)->nullable()->after('tuition_fee');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('fee_structures', 'girls_tuition_fee')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->dropColumn('girls_tuition_fee');
            });
        }
    }
}
