<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarkTypeToSubjectsTable extends Migration
{
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            // 'marks' = full breakdown (Oral/Activity/Test/HW/Writing)
            // 'grade_only' = just a grade entered directly (PE, Tabla, Dance etc.)
            $table->string('mark_type')->default('marks')->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('mark_type');
        });
    }
}
