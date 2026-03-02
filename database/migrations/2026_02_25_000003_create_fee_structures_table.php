<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructuresTable extends Migration
{
    public function up()
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->string('academic_year');
            $table->decimal('admission_fee', 10, 2)->default(0);
            $table->decimal('tuition_fee', 10, 2)->default(0);
            $table->decimal('transport_fee', 10, 2)->default(0);
            $table->decimal('other_fee', 10, 2)->default(0);
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('school_classes')->cascadeOnDelete();
            $table->unique(['class_id', 'academic_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_structures');
    }
}
