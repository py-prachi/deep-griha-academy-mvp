<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class AddAttendanceDateToAttendancesTable extends Migration
{
    public function up()
{
    Schema::table('attendances', function (Blueprint $table) {
        $table->date('attendance_date')->nullable()->after('status');
    });

    // Backfill existing records using created_at
    DB::statement('UPDATE attendances SET attendance_date = DATE(created_at) WHERE attendance_date IS NULL');
}


    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('attendance_date');
        });
    }
}
