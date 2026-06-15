<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassToCounsellingRemarksTable extends Migration
{
    public function up()
    {
        Schema::table('counselling_remarks', function (Blueprint $table) {
            $table->string('class_name', 50)->nullable()->after('remark_date');
            $table->string('section_name', 20)->nullable()->after('class_name');
        });
    }

    public function down()
    {
        Schema::table('counselling_remarks', function (Blueprint $table) {
            $table->dropColumn(['class_name', 'section_name']);
        });
    }
}
