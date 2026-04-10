<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks which mark components a student was absent for.
 * Stored as JSON array e.g. ["test","oral_written"]
 * The component still gets 0 in the mark column, but the UI shows "AB" for it.
 */
class AddAbsentComponentsToStudentTermMarks extends Migration
{
    public function up()
    {
        Schema::table('student_term_marks', function (Blueprint $table) {
            $table->text('absent_components')->nullable()->after('grade');
        });
    }

    public function down()
    {
        Schema::table('student_term_marks', function (Blueprint $table) {
            $table->dropColumn('absent_components');
        });
    }
}
