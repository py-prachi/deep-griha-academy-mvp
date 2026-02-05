<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleToNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->string('title')->nullable()->after('id');
    });
}

public function down()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->dropColumn('title');
    });
}

}
