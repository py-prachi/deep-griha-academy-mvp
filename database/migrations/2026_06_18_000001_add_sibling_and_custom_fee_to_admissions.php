<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiblingAndCustomFeeToAdmissions extends Migration
{
    public function up()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->decimal('custom_tuition_fee', 10, 2)->nullable()->after('discount_percentage');
            $table->unsignedBigInteger('sibling_admission_id')->nullable()->after('custom_tuition_fee');
            $table->text('fee_note')->nullable()->after('sibling_admission_id');

            $table->foreign('sibling_admission_id')
                  ->references('id')->on('admissions')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['sibling_admission_id']);
            $table->dropColumn(['custom_tuition_fee', 'sibling_admission_id', 'fee_note']);
        });
    }
}
