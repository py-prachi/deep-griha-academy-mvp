<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreschoolSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('preschool_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->unsignedInteger('sort_order');
            $table->string('activity_name', 200);
            $table->text('material')->nullable();
            $table->text('objective')->nullable();
            $table->text('actual_teach')->nullable();
            $table->text('assessment')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('preschool_plans')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('preschool_slots');
    }
}
