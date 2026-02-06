<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->longText('description')->nullable()->after('title');
    });
}

public function down()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->dropColumn('description');
    });
}

}
