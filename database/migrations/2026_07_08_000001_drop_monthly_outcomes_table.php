<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMonthlyOutcomesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('monthly_outcomes');
    }

    public function down()
    {
        Schema::create('monthly_outcomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedBigInteger('subject_id');
            $table->enum('month', [
                'april','june','july','august','september',
                'october','november','december','january','february','march'
            ]);
            $table->unsignedSmallInteger('number_of_days')->default(0);
            $table->string('chapter_number')->nullable();
            $table->string('chapter_name');
            $table->text('learning_outcomes')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }
}
