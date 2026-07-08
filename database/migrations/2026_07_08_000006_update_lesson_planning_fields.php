<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateLessonPlanningFields extends Migration
{
    public function up()
    {
        // Merge duration + flow_of_days into one field on plan_modules
        DB::statement('ALTER TABLE plan_modules ADD COLUMN duration_and_flow TEXT NULL AFTER objectives');
        DB::statement('ALTER TABLE plan_modules DROP COLUMN duration');
        DB::statement('ALTER TABLE plan_modules DROP COLUMN flow_of_days');

        // date_execution can be a range like "3/4/2025 & 4/4/2025"
        DB::statement('ALTER TABLE plan_lessons MODIFY COLUMN date_execution VARCHAR(200) NULL');

        // Status: planned (default) or completed
        DB::statement("ALTER TABLE plan_lessons ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'planned' AFTER remark");
    }

    public function down()
    {
        DB::statement('ALTER TABLE plan_modules ADD COLUMN duration VARCHAR(100) NULL AFTER objectives');
        DB::statement('ALTER TABLE plan_modules ADD COLUMN flow_of_days TEXT NULL AFTER duration');
        DB::statement('ALTER TABLE plan_modules DROP COLUMN duration_and_flow');
        DB::statement('ALTER TABLE plan_lessons MODIFY COLUMN date_execution DATE NULL');
        DB::statement('ALTER TABLE plan_lessons DROP COLUMN status');
    }
}
