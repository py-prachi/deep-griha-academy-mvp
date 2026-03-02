<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavingCertificatesTable extends Migration
{
    public function up()
    {
        Schema::create('leaving_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_user_id');
            $table->unsignedInteger('lc_number')->unique();
            $table->string('student_name');
            $table->string('mother_name')->nullable();
            $table->string('race_and_caste')->nullable();
            $table->string('nationality')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('last_school_attended')->nullable();
            $table->date('date_of_admission')->nullable();
            $table->string('standard_studying')->nullable();
            $table->string('in_standard_since')->nullable();
            $table->string('register_no')->nullable();
            $table->enum('progress', ['good', 'average', 'poor'])->nullable();
            $table->enum('conduct', ['good', 'satisfactory', 'poor'])->nullable();
            $table->date('date_of_leaving');
            $table->text('reason_for_leaving')->nullable();
            $table->text('remark')->nullable();
            $table->decimal('outstanding_balance_at_lc', 10, 2)->default(0);
            $table->boolean('fee_warning_acknowledged')->default(false);
            $table->unsignedBigInteger('issued_by');
            $table->date('issued_date');
            $table->timestamps();
            $table->foreign('student_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('issued_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaving_certificates');
    }
}
