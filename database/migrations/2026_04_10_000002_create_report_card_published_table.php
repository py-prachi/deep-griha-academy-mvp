<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportCardPublishedTable extends Migration
{
    public function up()
    {
        Schema::create('report_card_published', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedTinyInteger('term'); // 1 or 2
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'session_id', 'term']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_card_published');
    }
}
