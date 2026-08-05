<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolidaysTable extends Migration
{
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->foreignId('session_id')->constrained('school_sessions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['session_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('holidays');
    }
}
