<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounsellingRemarksTable extends Migration
{
    public function up()
    {
        Schema::create('counselling_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('counselling_id');
            $table->text('remark');
            $table->date('remark_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('counselling_id')->references('id')->on('student_counsellings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('counselling_remarks');
    }
}
