<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Extra dates the school is open (e.g. a Saturday) that would otherwise not
// count as a working day — the inverse of the holidays table.
class CreateSpecialSchoolDaysTable extends Migration
{
    public function up()
    {
        Schema::create('special_school_days', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name')->nullable();
            $table->foreignId('session_id')->constrained('school_sessions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['session_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('special_school_days');
    }
}
