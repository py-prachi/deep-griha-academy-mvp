<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddGraduatedToStudentStatus extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY student_status ENUM('active','left','graduated') NOT NULL DEFAULT 'active'");
        }
        // SQLite: column is already a string, 'graduated' value works without schema change
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET student_status = 'left' WHERE student_status = 'graduated'");
            DB::statement("ALTER TABLE users MODIFY student_status ENUM('active','left') NOT NULL DEFAULT 'active'");
        }
    }
}
