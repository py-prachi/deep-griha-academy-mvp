<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanLessonsTable extends Migration
{
    public function up()
    {
        Schema::create('plan_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('module_id')->nullable();
            $table->tinyInteger('module_required')->default(0);
            $table->date('date_written')->nullable();
            $table->date('date_execution')->nullable();
            $table->text('chapter_topic')->nullable();
            $table->string('period_timing', 100)->nullable();
            $table->text('learning_standard')->nullable();
            $table->text('objective')->nullable();
            $table->text('material_needed')->nullable();
            $table->text('training_component')->nullable();
            $table->text('student_responses')->nullable();
            $table->text('hook')->nullable();
            $table->text('teach')->nullable();
            $table->text('guided_practice')->nullable();
            $table->text('independent_practice')->nullable();
            $table->text('closure')->nullable();
            $table->text('homework')->nullable();
            $table->text('other_notes')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('plan_modules')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_lessons');
    }
}
