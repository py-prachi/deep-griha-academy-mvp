<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminRemarkToPlanModulesTable extends Migration
{
    public function up()
    {
        Schema::table('plan_modules', function (Blueprint $table) {
            $table->text('admin_remark')->nullable()->after('materials');
        });
    }

    public function down()
    {
        Schema::table('plan_modules', function (Blueprint $table) {
            $table->dropColumn('admin_remark');
        });
    }
}
