<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddExitedToUsersStudentStatusEnum extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN student_status ENUM('active','left','graduated','exited') NOT NULL DEFAULT 'active'");
    }

    public function down()
    {
        DB::statement("UPDATE users SET student_status = 'left' WHERE student_status = 'exited'");
        DB::statement("ALTER TABLE users MODIFY COLUMN student_status ENUM('active','left','graduated') NOT NULL DEFAULT 'active'");
    }
}
