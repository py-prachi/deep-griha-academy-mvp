<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeSettlementsTable extends Migration
{
    public function up()
    {
        Schema::create('fee_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_user_id');
            $table->unsignedBigInteger('session_id');
            $table->decimal('outstanding_amount', 10, 2)->default(0);
            $table->enum('settlement_type', ['waived', 'paid_offline', 'carried_forward', 'other']);
            $table->text('remark');
            $table->unsignedBigInteger('settled_by')->nullable();
            $table->timestamps();

            $table->unique(['student_user_id', 'session_id']);
            $table->foreign('student_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_settlements');
    }
}
