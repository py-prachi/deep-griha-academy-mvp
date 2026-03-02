<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissionDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('admission_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admission_id');
            $table->enum('document_type', [
                'birth_certificate',
                'previous_school_lc',
                'aadhaar_card',
                'passport_photos',
                'caste_certificate',
                'rte_documents',
            ]);
            $table->enum('status', ['received', 'pending', 'na'])->default('pending');
            $table->date('received_date')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->foreign('admission_id')->references('id')->on('admissions')->cascadeOnDelete();
            $table->unique(['admission_id', 'document_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admission_documents');
    }
}
