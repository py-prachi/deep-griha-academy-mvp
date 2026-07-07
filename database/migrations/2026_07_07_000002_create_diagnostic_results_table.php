<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiagnosticResultsTable extends Migration
{
    public function up()
    {
        Schema::create('diagnostic_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('teacher_id');
            $table->enum('assessment_type', [
                'diagnostic', 'unit_test_1', 'unit_test_2',
                'first_term', 'unit_test_3', 'second_term', 'annual'
            ]);
            $table->string('marks_obtained', 100)->nullable();
            $table->text('needs')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['session_id', 'class_id', 'section_id', 'subject_id', 'student_id', 'assessment_type'],
                'diagnostic_results_unique'
            );

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('diagnostic_results');
    }
}
