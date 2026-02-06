<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeNoticeNullableInNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->longText('notice')->nullable()->change();
    });
}

public function down()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->longText('notice')->nullable(false)->change();
    });
}

}
