<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddExitedStatusToAdmissionsTable extends Migration
{
    public function up()
    {
        // Add exit_date column
        Schema::table('admissions', function (Blueprint $table) {
            $table->date('exit_date')->nullable()->after('confirmed_date');
        });

        // Alter the status enum to include 'exited'
        DB::statement("ALTER TABLE admissions MODIFY COLUMN status ENUM('inquiry','pending','confirmed','cancelled','exited') NOT NULL DEFAULT 'inquiry'");
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('exit_date');
        });

        DB::statement("ALTER TABLE admissions MODIFY COLUMN status ENUM('inquiry','pending','confirmed','cancelled') NOT NULL DEFAULT 'inquiry'");
    }
}
