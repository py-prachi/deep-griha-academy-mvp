<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_user_id');
            $table->unsignedInteger('challan_no')->unique();
            $table->date('payment_date');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_mode', ['cash', 'cheque', 'qr'])->default('cash');
            $table->string('cheque_no')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->boolean('is_internal_transfer')->default(false);
            $table->unsignedBigInteger('recorded_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('student_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('recorded_by')->references('id')->on('users');
        });

        Schema::create('fee_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_payment_id');
            $table->enum('description', [
                'admission_fee',
                'tuition_fee',
                'other_fee',
                'transfer_certificate',
                'bonafide_certificate',
                'transport_charges',
                'stationery',
                'uniform',
                'sports',
                'notebooks',
            ]);
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_line_items');
        Schema::dropIfExists('fee_payments');
    }
}
