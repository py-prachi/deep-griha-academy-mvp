<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRteApplicationNoToAdmissions extends Migration
{
    public function up()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('rte_application_no', 20)->nullable()->after('fee_category');
        });
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('rte_application_no');
        });
    }
}
