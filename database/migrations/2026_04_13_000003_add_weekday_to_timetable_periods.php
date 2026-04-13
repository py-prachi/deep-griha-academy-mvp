<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeekdayToTimetablePeriods extends Migration
{
    public function up()
    {
        Schema::table('timetable_periods', function (Blueprint $table) {
            $table->tinyInteger('weekday')->default(0)->after('sort_order');
            // 0 = default (fallback for days with no custom periods), 1=Mon … 6=Sat
        });
    }

    public function down()
    {
        Schema::table('timetable_periods', function (Blueprint $table) {
            $table->dropColumn('weekday');
        });
    }
}
