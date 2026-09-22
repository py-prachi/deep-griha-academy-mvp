<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExitTypeToStudentExitsTable extends Migration
{
    public function up()
    {
        Schema::table('student_exits', function (Blueprint $table) {
            // Nullable on purpose: existing exit records predate this field and
            // need to be reviewed/categorized by an admin (see StudentExitController).
            // 'genuine'    = the student actually left the school (LC required).
            // 'correction' = the record was a mistake / class change etc. (no LC).
            $table->string('exit_type', 20)->nullable()->after('admission_id');
        });
    }

    public function down()
    {
        Schema::table('student_exits', function (Blueprint $table) {
            $table->dropColumn('exit_type');
        });
    }
}
