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
            $table->unsignedBigInteger('admission_id');

            // Certificate meta
            $table->string('lc_number', 20)->unique();
            $table->date('issue_date');
            $table->string('issue_place')->default('Pune');

            // Student info snapshotted at issue time
            $table->string('pupil_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('race_and_caste')->nullable();
            $table->string('nationality')->default('Indian');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();

            // Academic history
            $table->string('last_school_attended')->nullable();
            $table->date('date_of_admission')->nullable();
            $table->string('progress')->nullable();
            $table->string('conduct', 100)->default('Good');
            $table->date('date_of_leaving');
            $table->string('standard_studying', 100)->nullable();
            $table->date('studying_since')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->text('remarks')->nullable();

            // Fee snapshot
            $table->boolean('fees_cleared')->default(false);
            $table->decimal('fees_due', 10, 2)->default(0);

            // Audit
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaving_certificates');
    }
}
