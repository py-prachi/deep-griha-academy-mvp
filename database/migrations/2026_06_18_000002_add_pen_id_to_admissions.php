<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPenIdToAdmissions extends Migration
{
    public function up()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('pen_id')->nullable()->after('aadhaar_no');
        });
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('pen_id');
        });
    }
}
