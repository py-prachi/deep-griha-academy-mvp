<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTimetablePeriodsTable extends Migration
{
    public function up()
    {
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('start_time');
            $table->string('end_time');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_break')->default(false);
            $table->timestamps();
        });

        $periods = [
            ['label' => 'Period 1', 'start_time' => '08:00', 'end_time' => '08:45', 'sort_order' => 1, 'is_break' => false],
            ['label' => 'Period 2', 'start_time' => '08:45', 'end_time' => '09:30', 'sort_order' => 2, 'is_break' => false],
            ['label' => 'Period 3', 'start_time' => '09:30', 'end_time' => '10:15', 'sort_order' => 3, 'is_break' => false],
            ['label' => 'Break',    'start_time' => '10:15', 'end_time' => '10:30', 'sort_order' => 4, 'is_break' => true],
            ['label' => 'Period 4', 'start_time' => '10:30', 'end_time' => '11:15', 'sort_order' => 5, 'is_break' => false],
            ['label' => 'Period 5', 'start_time' => '11:15', 'end_time' => '12:00', 'sort_order' => 6, 'is_break' => false],
            ['label' => 'Lunch',    'start_time' => '12:00', 'end_time' => '12:30', 'sort_order' => 7, 'is_break' => true],
            ['label' => 'Period 6', 'start_time' => '12:30', 'end_time' => '13:15', 'sort_order' => 8, 'is_break' => false],
            ['label' => 'Period 7', 'start_time' => '13:15', 'end_time' => '14:00', 'sort_order' => 9, 'is_break' => false],
        ];

        foreach ($periods as $period) {
            DB::table('timetable_periods')->insert(array_merge($period, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('timetable_periods');
    }
}
