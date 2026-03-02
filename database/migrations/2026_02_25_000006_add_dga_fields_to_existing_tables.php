<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDgaFieldsToExistingTables extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('admission_id')->nullable()->after('id');
            $table->enum('fee_category', ['general', 'rte', 'coc', 'discount'])->nullable()->after('admission_id');
            $table->string('dga_admission_no')->nullable()->unique()->after('fee_category');
            $table->string('general_id')->nullable()->unique()->after('dga_admission_no');
            $table->string('village')->nullable()->after('general_id');
            $table->string('distance_from_school')->nullable()->after('village');
            $table->enum('student_status', ['active', 'left'])->default('active')->after('distance_from_school');
            $table->date('date_of_leaving')->nullable()->after('student_status');
        });

        Schema::table('student_parent_infos', function (Blueprint $table) {
            $table->string('father_occupation')->nullable()->after('father_phone');
            $table->string('mother_occupation')->nullable()->after('mother_phone');
            $table->string('guardian_name')->nullable()->after('parent_address');
            $table->string('guardian_occupation')->nullable()->after('guardian_name');
            $table->text('guardian_address')->nullable()->after('guardian_occupation');
            $table->string('sibling_name_age')->nullable()->after('guardian_address');
            $table->string('contact_emergency')->nullable()->after('sibling_name_age');
            $table->string('doctor_name_phone')->nullable()->after('contact_emergency');
            $table->text('allergies_medical')->nullable()->after('doctor_name_phone');
            $table->boolean('transport_required')->default(false)->after('allergies_medical');
        });

        Schema::table('student_academic_infos', function (Blueprint $table) {
            $table->string('previous_school')->nullable()->after('board_reg_no');
            $table->string('language_spoken_at_home')->nullable()->after('previous_school');
            $table->string('place_of_birth')->nullable()->after('language_spoken_at_home');
            $table->string('nationality')->default('Indian')->after('place_of_birth');
            $table->string('caste')->nullable()->after('nationality');
            $table->string('religion')->nullable()->after('caste');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'admission_id', 'fee_category', 'dga_admission_no', 'general_id',
                'village', 'distance_from_school', 'student_status', 'date_of_leaving'
            ]);
        });

        Schema::table('student_parent_infos', function (Blueprint $table) {
            $table->dropColumn([
                'father_occupation', 'mother_occupation', 'guardian_name',
                'guardian_occupation', 'guardian_address', 'sibling_name_age',
                'contact_emergency', 'doctor_name_phone', 'allergies_medical',
                'transport_required'
            ]);
        });

        Schema::table('student_academic_infos', function (Blueprint $table) {
            $table->dropColumn([
                'previous_school', 'language_spoken_at_home', 'place_of_birth',
                'nationality', 'caste', 'religion'
            ]);
        });
    }
}
