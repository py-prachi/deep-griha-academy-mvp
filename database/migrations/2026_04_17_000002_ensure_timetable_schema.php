<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureTimetableSchema extends Migration
{
    public function up()
    {
        // Ensure timetable_periods table exists
        if (!Schema::hasTable('timetable_periods')) {
            Schema::create('timetable_periods', function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->string('start_time', 5)->default('08:00');
                $table->string('end_time', 5)->default('08:45');
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->boolean('is_break')->default(false);
                $table->unsignedTinyInteger('weekday')->default(0);
                $table->timestamps();
            });
        }

        // Ensure weekday column exists on timetable_periods
        if (Schema::hasTable('timetable_periods') && !Schema::hasColumn('timetable_periods', 'weekday')) {
            Schema::table('timetable_periods', function (Blueprint $table) {
                $table->unsignedTinyInteger('weekday')->default(0)->after('is_break');
            });
        }

        // Ensure period_id column exists on routines
        if (!Schema::hasColumn('routines', 'period_id')) {
            Schema::table('routines', function (Blueprint $table) {
                $table->unsignedBigInteger('period_id')->nullable()->after('course_id');
                $table->foreign('period_id')->references('id')->on('timetable_periods')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // intentionally left empty — don't drop on rollback
    }
}
