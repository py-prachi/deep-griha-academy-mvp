<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddGraduatedToAdmissionsStatus extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE admissions MODIFY status ENUM('inquiry','pending','confirmed','cancelled','exited','graduated') NOT NULL DEFAULT 'inquiry'");
    }

    public function down()
    {
        DB::statement("UPDATE admissions SET status = 'confirmed' WHERE status = 'graduated'");
        DB::statement("ALTER TABLE admissions MODIFY status ENUM('inquiry','pending','confirmed','cancelled','exited') NOT NULL DEFAULT 'inquiry'");
    }
}
