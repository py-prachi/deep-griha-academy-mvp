<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBloodTypeToAdmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('blood_type')->nullable()->after('allergies_medical');
        });
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('blood_type');
        });
    }
}
