<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanModulesTable extends Migration
{
    public function up()
    {
        Schema::create('plan_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->date('date_written')->nullable();
            $table->text('topic');
            $table->text('learning_outcome')->nullable();
            $table->text('assessment')->nullable();
            $table->text('rubric')->nullable();
            $table->text('objectives')->nullable();
            $table->string('duration', 100)->nullable();
            $table->text('flow_of_days')->nullable();
            $table->text('materials')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_modules');
    }
}
