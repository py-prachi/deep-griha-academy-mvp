<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkFeeReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('bulk_fee_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->enum('fee_category', ['rte', 'coc']);
            $table->decimal('amount_received', 10, 2);
            $table->date('received_date');
            $table->string('remark')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bulk_fee_receipts');
    }
}
