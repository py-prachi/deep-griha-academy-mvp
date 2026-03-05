<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactFieldsToAdmissionsTable extends Migration
{
    public function up()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('father_phone')->nullable()->after('father_occupation');
            $table->string('mother_phone')->nullable()->after('mother_occupation');
            $table->string('city')->nullable()->after('full_address');
            $table->string('zip')->nullable()->after('city');
        });
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['father_phone', 'mother_phone', 'city', 'zip']);
        });
    }
}
