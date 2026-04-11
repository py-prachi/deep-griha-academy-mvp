<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentObservationsTable extends Migration
{
    public function up()
    {
        Schema::create('student_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedTinyInteger('term'); // 1 or 2
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_id', 'term']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_observations');
    }
}
