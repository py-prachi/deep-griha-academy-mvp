<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNarrativeFieldsToStudentObservations extends Migration
{
    public function up()
    {
        Schema::table('student_observations', function (Blueprint $table) {
            $table->text('does_well_in')->nullable()->after('remarks');
            $table->text('needs_improvement')->nullable()->after('does_well_in');
        });
    }

    public function down()
    {
        Schema::table('student_observations', function (Blueprint $table) {
            $table->dropColumn(['does_well_in', 'needs_improvement']);
        });
    }
}
