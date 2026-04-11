<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTermMarksTable extends Migration
{
    public function up()
    {
        Schema::create('student_term_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('session_id');
            $table->tinyInteger('term'); // 1 or 2

            // Internal assessment components (nullable — null = subject is grade_only or NA)
            $table->decimal('oral_internal', 5, 2)->nullable();     // max varies by class group
            $table->decimal('activity_internal', 5, 2)->nullable();
            $table->decimal('test', 5, 2)->nullable();
            $table->decimal('hw', 5, 2)->nullable();

            // Written components
            $table->decimal('oral_written', 5, 2)->nullable();
            $table->decimal('activity_written', 5, 2)->nullable();
            $table->decimal('writing', 5, 2)->nullable();

            // Calculated / stored totals
            $table->decimal('internal_total', 5, 2)->nullable();    // sum of internal components
            $table->decimal('written_total', 5, 2)->nullable();     // sum of written components
            $table->decimal('grand_total', 5, 2)->nullable();       // internal + written

            // Final grade (auto-calculated for marks subjects, manually entered for grade_only)
            // Special values: 'NA' = not applicable, null = not yet entered
            $table->string('grade', 5)->nullable();

            // Who entered / verified
            $table->unsignedBigInteger('entered_by')->nullable();   // subject teacher user_id
            $table->timestamp('entered_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();  // CT user_id
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // One row per student per subject per term per session
            $table->unique(
                ['student_id', 'subject_id', 'session_id', 'term'],
                'stm_unique'
            );

            $table->foreign('student_id')->references('id')->on('users');
            $table->foreign('subject_id')->references('id')->on('subjects');
            $table->foreign('session_id')->references('id')->on('school_sessions');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_term_marks');
    }
}
