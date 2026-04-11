<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the date on which each exam component was conducted.
 * One row per subject+class+session+term+component.
 * Shared across all students — teacher enters once.
 *
 * Components for 'marks' type subjects:
 *   oral_internal, activity_internal, test, hw,
 *   oral_written, activity_written, writing
 *
 * Component for 'grade_only' type subjects:
 *   exam (single date for the assessment)
 */
class CreateMarkExamDatesTable extends Migration
{
    public function up()
    {
        Schema::create('mark_exam_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('session_id');
            $table->unsignedTinyInteger('term');           // 1 or 2
            $table->string('component', 30);               // oral_internal, test, hw, etc.
            $table->date('exam_date');
            $table->timestamps();

            $table->unique(['subject_id', 'class_id', 'session_id', 'term', 'component'], 'med_unique');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mark_exam_dates');
    }
}
