<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherAssignmentsTable extends Migration
{
    public function up()
    {
        // Class teacher: 1 per section per session
        Schema::create('class_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedInteger('session_id');
            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'session_id']); // 1 class teacher per section
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Subject teacher: teacher → subject → class → section
        Schema::create('subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedInteger('session_id');
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id', 'class_id', 'section_id', 'session_id'], 'subj_teacher_unique');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_teachers');
        Schema::dropIfExists('class_teachers');
    }
}
