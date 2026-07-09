<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledDateToPlanLessons extends Migration
{
    public function up()
    {
        Schema::table('plan_lessons', function (Blueprint $table) {
            // Machine-readable teaching date for timetable integration
            $table->date('scheduled_date')->nullable()->after('date_execution');
            // Link to specific timetable period slot (optional)
            $table->unsignedBigInteger('period_id')->nullable()->after('scheduled_date');
            $table->foreign('period_id')->references('id')->on('timetable_periods')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('plan_lessons', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn(['scheduled_date', 'period_id']);
        });
    }
}
