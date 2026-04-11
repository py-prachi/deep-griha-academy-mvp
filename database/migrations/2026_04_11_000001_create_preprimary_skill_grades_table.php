<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreprimarySkillGradesTable extends Migration
{
    public function up()
    {
        Schema::create('preprimary_skill_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedTinyInteger('term'); // 1 or 2
            $table->string('skill_code', 60);
            $table->char('grade', 1)->nullable(); // E / S / I / D
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_id', 'term', 'skill_code'], 'pp_skill_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('preprimary_skill_grades');
    }
}
