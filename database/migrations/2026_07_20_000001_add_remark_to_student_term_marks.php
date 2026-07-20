<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarkToStudentTermMarks extends Migration
{
    public function up()
    {
        Schema::table('student_term_marks', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('absent_components');
        });
    }

    public function down()
    {
        Schema::table('student_term_marks', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
}
