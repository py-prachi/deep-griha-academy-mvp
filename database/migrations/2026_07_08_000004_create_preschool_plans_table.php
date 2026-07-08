<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreschoolPlansTable extends Migration
{
    public function up()
    {
        Schema::create('preschool_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->date('plan_date');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('preschool_plans');
    }
}
