<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodIdToRoutinesTable extends Migration
{
    public function up()
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->unsignedBigInteger('period_id')->nullable()->after('course_id');
            $table->foreign('period_id')->references('id')->on('timetable_periods')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
}
