<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // Marathi, English, Maths...
            $table->string('code')->nullable(); // MAR, ENG, MTH
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Which subjects are taught in which class (can vary by class)
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('session_id');
            $table->timestamps();

            $table->unique(['subject_id', 'class_id', 'session_id']);
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
    }
}
