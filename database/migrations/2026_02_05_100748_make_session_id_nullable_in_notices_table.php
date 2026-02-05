<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSessionIdNullableInNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->unsignedBigInteger('session_id')
              ->nullable()
              ->change();
    });
}

public function down()
{
    Schema::table('notices', function (Blueprint $table) {
        $table->unsignedBigInteger('session_id')
              ->nullable(false)
              ->change();
    });
}

}
