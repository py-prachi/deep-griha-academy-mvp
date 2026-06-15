<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountPercentageToAdmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discounted_amount');
        });
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });
    }
}
