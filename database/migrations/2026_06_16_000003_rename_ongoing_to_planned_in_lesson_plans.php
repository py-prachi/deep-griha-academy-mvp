<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameOngoingToPlannedInLessonPlans extends Migration
{
    public function up()
    {
        // Expand enum to include both old and new value, then migrate data, then narrow
        // (MODIFY COLUMN / ENUM is MySQL only — SQLite has no enum constraint at all)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lesson_plans MODIFY COLUMN status ENUM('ongoing','planned','completed') NOT NULL DEFAULT 'planned'");
        }
        DB::statement("UPDATE lesson_plans SET status = 'planned' WHERE status = 'ongoing'");
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lesson_plans MODIFY COLUMN status ENUM('planned','completed') NOT NULL DEFAULT 'planned'");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lesson_plans MODIFY COLUMN status ENUM('planned','ongoing','completed') NOT NULL DEFAULT 'ongoing'");
        }
        DB::statement("UPDATE lesson_plans SET status = 'ongoing' WHERE status = 'planned'");
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lesson_plans MODIFY COLUMN status ENUM('ongoing','completed') NOT NULL DEFAULT 'ongoing'");
        }
    }
}
