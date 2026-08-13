<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminRemarkToLessonPlansTable extends Migration
{
    public function up()
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->text('admin_remark')->nullable()->after('learning_standards');
        });
    }

    public function down()
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropColumn('admin_remark');
        });
    }
}
