<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['inquiry', 'pending', 'confirmed', 'cancelled'])->default('inquiry');
            $table->text('cancel_reason')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('academic_year');
            $table->string('dga_admission_no')->nullable()->unique();
            $table->string('general_id')->nullable()->unique();
            $table->unsignedBigInteger('student_user_id')->nullable();
            $table->enum('fee_category', ['general', 'rte', 'coc', 'discount'])->nullable();
            $table->decimal('discounted_amount', 10, 2)->nullable();
            $table->string('student_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('caste')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->default('Indian');
            $table->string('place_of_birth')->nullable();
            $table->string('language_spoken_at_home')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->text('full_address')->nullable();
            $table->string('village')->nullable();
            $table->string('distance_from_school')->nullable();
            $table->string('contact_residence')->nullable();
            $table->string('contact_mobile')->nullable();
            $table->string('contact_emergency')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('sibling_name_age')->nullable();
            $table->boolean('transport_required')->default(false);
            $table->text('allergies_medical')->nullable();
            $table->string('doctor_name_phone')->nullable();
            $table->string('previous_school')->nullable();
            $table->date('inquiry_date');
            $table->date('confirmed_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('session_id')->references('id')->on('school_sessions')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('student_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admissions');
    }
}
