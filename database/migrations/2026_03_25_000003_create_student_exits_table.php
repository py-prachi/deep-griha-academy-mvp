<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentExitsTable extends Migration
{
    public function up()
    {
        Schema::create('student_exits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id')->unique(); // one exit per student
            $table->date('exit_date');
            $table->text('reason_for_leaving')->nullable();
            $table->text('liked_most')->nullable();
            $table->text('liked_least')->nullable();
            $table->text('suggestions')->nullable();
            $table->tinyInteger('rating')->unsigned()->nullable(); // 1–5
            $table->string('parent_name')->nullable();
            $table->string('parent_contact')->nullable();
            $table->string('staff_name')->nullable();
            $table->date('form_submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_exits');
    }
}
